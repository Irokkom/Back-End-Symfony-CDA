<?php

namespace App\Tests\Integration;

use App\Controller\HomeController;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\MongoDBService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Test d'intégration HomeController + MongoDB
class HomeControllerMongoDBIntegrationTest extends TestCase
{
    private $homeController;
    private $mongoDBService;

    protected function setUp(): void
    {
        // Mocks des dépendances
        $articleRepo = $this->createMock(ArticleRepository::class);
        $categoryRepo = $this->createMock(CategoryRepository::class);
        $this->mongoDBService = $this->createMock(MongoDBService::class);
        
        // Config MongoDB pour vérifier l'interaction
        $this->mongoDBService->expects($this->once())
            ->method('insertVisit')
            ->with('home_page');
        
        // Mock du contrôleur
        $this->homeController = $this->getMockBuilder(HomeController::class)
            ->setConstructorArgs([$articleRepo, $categoryRepo, $this->mongoDBService])
            ->onlyMethods(['render', 'createForm', 'generateUrl'])
            ->getMock();
        
        // Config minimale des retours du contrôleur
        $form = $this->createMock(FormInterface::class);
        $form->method('createView')->willReturn($this->createMock(FormView::class));
        
        $this->homeController->method('createForm')->willReturn($form);
        $this->homeController->method('render')->willReturn(new Response());
        $this->homeController->method('generateUrl')->willReturn('/search');
    }
    
    public function testHomeControllerUsesMongoDBService(): void
    {
        // Test
        $request = Request::create('/');
        $method = new \ReflectionMethod(HomeController::class, 'index');
        $method->setAccessible(true);
        $response = $method->invoke($this->homeController, $request);
        
        $this->assertInstanceOf(Response::class, $response);
        // Le vrai test est dans le setUp (expects)
    }
}
