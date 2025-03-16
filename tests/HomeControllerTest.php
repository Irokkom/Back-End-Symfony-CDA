<?php

namespace App\Tests\Controller;

use App\Controller\HomeController;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\MongoDBService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomeControllerTest extends TestCase
{
    public function testIndex(): void
    {
        // Création des mocks pour les dépendances
        $articleRepository = $this->createMock(ArticleRepository::class);
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $mongoDBService = $this->createMock(MongoDBService::class);
        
        // Configuration des mocks
        $articleRepository->expects($this->once())
            ->method('findLatest')
            ->with(4)
            ->willReturn([]);
        
        $articleRepository->expects($this->once())
            ->method('findLatestExcept')
            ->willReturn([]);
            
        $categoryRepository->expects($this->once())
            ->method('findAllWithArticles')
            ->willReturn([]);
            
        $mongoDBService->expects($this->once())
            ->method('insertVisit')
            ->with('home_page');

        // Création du mock de HomeController
        $controller = $this->getMockBuilder(HomeController::class)
            ->setConstructorArgs([$articleRepository, $categoryRepository, $mongoDBService])
            ->onlyMethods(['render', 'createForm', 'generateUrl'])
            ->getMock();
        
        // Mock du formulaire
        $formView = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);
        
        // Configuration du mock du controller
        $controller->expects($this->once())
            ->method('createForm')
            ->willReturn($form);
            
        $controller->expects($this->once())
            ->method('generateUrl')
            ->with('app_search')
            ->willReturn('/recherche');
            
        $controller->expects($this->once())
            ->method('render')
            ->with(
                'home/index.html.twig',
                $this->callback(function ($params) use ($formView) {
                    return isset($params['featured_articles']) && 
                           isset($params['latest_articles']) &&
                           isset($params['categories']) &&
                           isset($params['search_form']) &&
                           $params['search_form'] === $formView;
                })
            )
            ->willReturn(new Response());
        
        // Création d'une requête sans paramètre de recherche
        $request = new Request();
        
        // Invoque la méthode index via reflection car le mock ne peut pas l'exposer directement
        $method = new \ReflectionMethod(HomeController::class, 'index');
        $method->setAccessible(true);
        $response = $method->invoke($controller, $request);
        
        // Vérification que la réponse est une instance de Response
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSearch(): void
    {
        // Création des mocks pour les dépendances
        $articleRepository = $this->createMock(ArticleRepository::class);
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $mongoDBService = $this->createMock(MongoDBService::class);
        
        // Création du mock de HomeController avec méthode redirectToRoute
        $controller = $this->getMockBuilder(HomeController::class)
            ->setConstructorArgs([$articleRepository, $categoryRepository, $mongoDBService])
            ->onlyMethods(['redirectToRoute'])
            ->getMock();
        
        // Configuration du mock
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                'app_search',
                ['q' => 'test']
            )
            ->willReturn(new RedirectResponse('/search?q=test'));
        
        // Création d'une requête avec paramètre de recherche
        $request = new Request(['q' => 'test']);
        
        // Invoque la méthode index via reflection car le mock ne peut pas l'exposer directement
        $method = new \ReflectionMethod(HomeController::class, 'index');
        $method->setAccessible(true);
        $response = $method->invoke($controller, $request);
        
        // Vérification que la réponse est une instance de Response
        $this->assertInstanceOf(Response::class, $response);
    }
}
