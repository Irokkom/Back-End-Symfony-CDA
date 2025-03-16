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

class HomeControllerMongoDBIntegrationTest extends TestCase
{
    private $homeController;
    private $articleRepository;
    private $categoryRepository;
    private $mongoDBService;

    protected function setUp(): void
    {
        // Création des mocks pour les dépendances
        $this->articleRepository = $this->createMock(ArticleRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->mongoDBService = $this->createMock(MongoDBService::class);
        
        // Configuration du mock pour MongoDBService
        $this->mongoDBService->expects($this->once())
            ->method('insertVisit')
            ->with('home_page');
        
        // Création du HomeController avec les mocks de base
        $this->homeController = $this->getMockBuilder(HomeController::class)
            ->setConstructorArgs([
                $this->articleRepository,
                $this->categoryRepository,
                $this->mongoDBService
            ])
            ->onlyMethods(['render', 'redirectToRoute', 'createForm', 'generateUrl'])
            ->getMock();
        
        // Configuration des mocks pour les méthodes du controller
        $formView = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())
            ->method('createView')
            ->willReturn($formView);
        
        $this->homeController->expects($this->any())
            ->method('createForm')
            ->willReturn($form);
            
        $this->homeController->expects($this->any())
            ->method('render')
            ->willReturn(new Response());
            
        $this->homeController->expects($this->any())
            ->method('generateUrl')
            ->willReturn('/search');
    }
    
    public function testHomeControllerUsesMongoDBService(): void
    {
        // Création d'une requête sans paramètre de recherche
        $request = Request::create('/');
        
        // Invoque la méthode index via reflection
        $method = new \ReflectionMethod(HomeController::class, 'index');
        $method->setAccessible(true);
        $response = $method->invoke($this->homeController, $request);
        
        // Vérifier que la réponse est bien une instance de Response
        $this->assertInstanceOf(Response::class, $response);
    }
}
