<?php

namespace App\Tests\Integration;

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

class HomeControllerMongoDBIntegrationTest extends TestCase
{
    /**
     * @var ArticleRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $articleRepository;
    
    /**
     * @var CategoryRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $categoryRepository;
    
    /**
     * @var SearchService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchService;
    
    /**
     * @var MongoDBService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mongoDBService;
    
    /**
     * @var HomeController
     */
    private $homeController;
    
    protected function setUp(): void
    {
        // Création des mocks pour les dépendances
        $this->articleRepository = $this->createMock(ArticleRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->searchService = $this->createMock(SearchService::class);
        $this->mongoDBService = $this->createMock(MongoDBService::class);
        
        // Création du HomeController avec les mocks
        $this->homeController = new HomeController(
            $this->articleRepository,
            $this->categoryRepository,
            $this->searchService,
            $this->mongoDBService
        );
    }
    
    public function testHomeControllerUsesMongoDBService(): void
    {
        // Configuration des attentes pour le mock MongoDBService
        $this->mongoDBService
            ->expects($this->once())
            ->method('insertVisit')
            ->with('home_page');
        
        // Création d'un mock pour FormInterface et FormView
        $formView = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->method('createView')->willReturn($formView);
            
        // Configuration des attentes pour le mock SearchService
        $this->searchService
            ->expects($this->once())
            ->method('handleSearchForm')
            ->willReturn(['form' => $form, 'results' => []]);
            
        // Configuration du mock ArticleRepository
        $this->articleRepository
            ->expects($this->once())
            ->method('findLatest')
            ->with(4)
            ->willReturn([]);
            
        // Configuration du mock CategoryRepository
        $this->categoryRepository
            ->expects($this->once())
            ->method('findAllWithArticles')
            ->willReturn([]);
        
        // Création d'une requête simulée
        $request = new Request();
        
        // Création d'une classe de test qui étend HomeController pour pouvoir mocker la méthode render
        $mockHomeController = $this->getMockBuilder(HomeController::class)
            ->setConstructorArgs([
                $this->articleRepository,
                $this->categoryRepository,
                $this->searchService,
                $this->mongoDBService
            ])
            ->onlyMethods(['render'])
            ->getMock();
            
        // Configuration du mock pour la méthode render
        $mockHomeController
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('home/index.html.twig'),
                $this->anything()
            )
            ->willReturn(new Response());
        
        // Appel de la méthode index du HomeController
        $response = $mockHomeController->index($request);
        
        // Vérification que la réponse est une instance de Response
        $this->assertInstanceOf(Response::class, $response);
    }
}
