<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testArticleSettersAndGetters(): void
    {
        $article = new Article();

        $article->setTitle('Test Title');
        $this->assertSame('Test Title', $article->getTitle());

    }
}
