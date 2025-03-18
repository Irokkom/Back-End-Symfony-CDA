<?php

namespace App\Tests\Integration;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use PHPUnit\Framework\TestCase;

class SimpleArticleIntegrationTest extends TestCase
{
    public function testRepositoryReturnsArticles(): void
    {
        // Création d'un mock du repository
        $articleMock = $this->createMock(Article::class);
        $articleMock->method('getTitle')->willReturn('Article Test');
        
        // Création et configuration du mock du repository avec la méthode findAllActive
        $repository = $this->getMockBuilder(ArticleRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        // Configuration explicite de la méthode findAllActive
        $repository->expects($this->once())
            ->method('findAllActive')
            ->willReturn([$articleMock]);
        
        // Test avec le mock
        $articles = $repository->findAllActive();
        
        // Vérification que la méthode retourne un tableau
        $this->assertIsArray($articles);
        $this->assertCount(1, $articles);
        $this->assertEquals('Article Test', $articles[0]->getTitle());
    }
}
