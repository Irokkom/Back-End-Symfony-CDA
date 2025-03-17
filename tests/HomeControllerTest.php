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

// Test unitaire du contrôleur
class HomeControllerTest extends TestCase
{
    public function testIndex(): void
    {
        // Mocks des dépendances
        $articleRepo = $this->createMock(ArticleRepository::class);
        $categoryRepo = $this->createMock(CategoryRepository::class);
        $mongoService = $this->createMock(MongoDBService::class);
        
        // Config des mocks
        $articleRepo->method('findLatest')->willReturn([]);
        $articleRepo->method('findLatestExcept')->willReturn([]);
        $categoryRepo->method('findAllWithArticles')->willReturn([]);
        $mongoService->method('insertVisit')->with('home_page');

        // Mock du contrôleur
        $controller = $this->getMockBuilder(HomeController::class)
            ->setConstructorArgs([$articleRepo, $categoryRepo, $mongoService])
            ->onlyMethods(['render', 'createForm', 'generateUrl'])
            ->getMock();
        
        // Mock du formulaire
        $formView = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->method('createView')->willReturn($formView);
        
        // Config du contrôleur
        $controller->method('createForm')->willReturn($form);
        $controller->method('generateUrl')->willReturn('/recherche');
        $controller->method('render')->willReturn(new Response());
        
        // Test
        $request = new Request();
        $method = new \ReflectionMethod(HomeController::class, 'index');
        $method->setAccessible(true);
        $response = $method->invoke($controller, $request);
        
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSearch(): void
    {
        // Mocks
        $articleRepo = $this->createMock(ArticleRepository::class);
        $categoryRepo = $this->createMock(CategoryRepository::class);
        $mongoService = $this->createMock(MongoDBService::class);
        
        // Mock du contrôleur
        $controller = $this->getMockBuilder(HomeController::class)
            ->setConstructorArgs([$articleRepo, $categoryRepo, $mongoService])
            ->onlyMethods(['redirectToRoute'])
            ->getMock();
        
        // Config du contrôleur
        $controller->method('redirectToRoute')
            ->with('app_search', ['q' => 'test'])
            ->willReturn(new RedirectResponse('/search?q=test'));
        
        // Test
        $request = new Request(['q' => 'test']);
        $method = new \ReflectionMethod(HomeController::class, 'index');
        $method->setAccessible(true);
        $response = $method->invoke($controller, $request);
        
        $this->assertInstanceOf(Response::class, $response);
    }
}
