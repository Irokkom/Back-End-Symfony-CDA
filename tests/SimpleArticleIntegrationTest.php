<?php

namespace App\Tests\Integration;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SimpleArticleIntegrationTest extends KernelTestCase
{
    public function testRepositoryReturnsArticles(): void
    {
        // Démarrage du kernel Symfony
        self::bootKernel();
        
        // Récupération du repository depuis le conteneur
        $container = static::getContainer();
        $articleRepository = $container->get(ArticleRepository::class);
        
        // Récupération des articles actifs
        $articles = $articleRepository->findAllActive();
        
        // Simple vérification que la méthode retourne un tableau
        $this->assertIsArray($articles);
    }
}
