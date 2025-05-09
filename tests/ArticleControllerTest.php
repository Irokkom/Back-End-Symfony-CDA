<?php

namespace App\Tests\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{
    public function testListeArticles(): void
    {
        // Mock du repository d articles
        $articleMock = $this->createMock(Article::class);
        $articleMock->method("getTitle")->willReturn("Article Test");
        $articleMock->method("getContent")->willReturn("Contenu de test");
        
        $repositoryMock = $this->createMock(ArticleRepository::class);
        $repositoryMock->method("findAllActive")->willReturn([$articleMock]);

        // Remplace le service réel par notre mock
        self::getContainer()->set(ArticleRepository::class, $repositoryMock);
        
        // Création d un client HTTP pour simuler des requêtes
        $client = static::createClient();
        
        // Envoi d une requête GET à la page de liste des articles
        $client->request("GET", "/articles");
        
        // Vérification que la réponse est réussie (code 200)
        $this->assertResponseIsSuccessful();
        
        // Vérification du contenu de la page
        $this->assertSelectorExists(".articles-container");
    }
}

