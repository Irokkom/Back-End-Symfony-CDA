<?php

namespace App\Tests\Controller;

use App\Controller\HomeController;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\MongoDBService;
use App\Service\SearchService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends TestCase
{
    private $homeController;
    private $articleRepository;
    private $categoryRepository;
    private $searchService;
    private $mongoDBService;

    protected function setUp(): void
    {
        // Création des mocks pour les dépendances
        $this->articleRepository = $this->createMock(ArticleRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->searchService = $this->createMock(SearchService::class);
        $this->mongoDBService = $this->createMock(MongoDBService::class);
        
        // Création du HomeController avec les mocks
        $this->homeController = $this->getMockBuilder(HomeController::class)
            ->setConstructorArgs([
                $this->articleRepository,
                $this->categoryRepository,
                $this->searchService,
                $this->mongoDBService
            ])
            ->onlyMethods(['render'])
            ->getMock();
    }

    public function testIndex(): void
    {
        // Création d'un mock pour FormInterface et FormView
        $formView = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->method('createView')->willReturn($formView);
        
        // Configuration des attentes pour les mocks
        $this->mongoDBService
            ->expects($this->once())
            ->method('insertVisit')
            ->with('home_page');
            
        $this->searchService
            ->expects($this->once())
            ->method('handleSearchForm')
            ->willReturn(['form' => $form, 'results' => []]);
            
        $this->articleRepository
            ->expects($this->once())
            ->method('findLatest')
            ->with(4)
            ->willReturn([]);
            
        $this->categoryRepository
            ->expects($this->once())
            ->method('findAllWithArticles')
            ->willReturn([]);
            
        // Configuration du mock pour la méthode render
        $this->homeController
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('home/index.html.twig'),
                $this->anything()
            )
            ->willReturn(new Response());
            
        // Création d'une requête simulée
        $request = new Request();
        
        // Appel de la méthode index du HomeController
        $response = $this->homeController->index($request);
        
        // Vérification que la réponse est une instance de Response
        $this->assertInstanceOf(Response::class, $response);
    }
    
    public function testSearch(): void
    {
        // Ce test est similaire à testIndex mais se concentre sur la fonctionnalité de recherche
        // Pour simplifier, nous allons juste vérifier que le service de recherche est appelé correctement
        
        // Création d'un mock pour FormInterface et FormView
        $formView = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->method('createView')->willReturn($formView);
        
        // Configuration des attentes pour les mocks
        $this->mongoDBService
            ->expects($this->once())
            ->method('insertVisit')
            ->with('home_page');
            
        // Simuler une recherche avec des résultats
        $searchResults = ['article1', 'article2'];
        $this->searchService
            ->expects($this->once())
            ->method('handleSearchForm')
            ->willReturn(['form' => $form, 'results' => $searchResults]);
            
        // Dans ce cas, findLatest ne devrait pas être appelé car nous avons des résultats de recherche
        $this->articleRepository
            ->expects($this->never())
            ->method('findLatest');
            
        $this->categoryRepository
            ->expects($this->once())
            ->method('findAllWithArticles')
            ->willReturn([]);
            
        // Configuration du mock pour la méthode render
        $this->homeController
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('home/index.html.twig'),
                $this->callback(function($parameters) use ($searchResults) {
                    return $parameters['search_results'] === $searchResults;
                })
            )
            ->willReturn(new Response());
            
        // Création d'une requête simulée avec un paramètre de recherche
        $request = new Request(['q' => 'test']);
        
        // Appel de la méthode index du HomeController
        $response = $this->homeController->index($request);
        
        // Vérification que la réponse est une instance de Response
        $this->assertInstanceOf(Response::class, $response);
    }
}
