<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{
    public function testListeArticles(): void
    {
        // Création d'un client HTTP pour simuler des requêtes
        $client = static::createClient();
        
        // Envoi d'une requête GET à la page de liste des articles
        $client->request('GET', '/articles');
        
        // Vérification que la réponse est réussie (code 200)
        $this->assertResponseIsSuccessful();
        
        // Vérification du contenu de la page
        $this->assertSelectorExists('.articles-container');
    }
}