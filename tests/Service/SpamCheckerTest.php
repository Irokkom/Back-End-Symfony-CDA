<?php

namespace App\Tests\Service;

use App\Entity\Comment;
use App\Service\SpamChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\Limiter;

class SpamCheckerTest extends TestCase
{
    private SpamChecker $spamChecker;
    private $rateLimiter;

    protected function setUp(): void
    {
        $storage = new InMemoryStorage();
        $factory = $this->createMock(RateLimiterFactory::class);
        $limiter = $this->createMock(Limiter::class);
        $rateLimit = new RateLimit(1, new \DateTimeImmutable(), true, 1);
        
        $limiter->method('consume')->willReturn($rateLimit);
        $factory->method('create')->willReturn($limiter);
        
        $this->spamChecker = new SpamChecker($factory);
    }

    public function testSpamKeywords(): void
    {
        $comment = new Comment();
        $comment->setContent('Buy viagra now!');

        $this->assertTrue(
            $this->spamChecker->isSpam($comment, '127.0.0.1'),
            'Le message contenant "viagra" devrait être détecté comme spam'
        );
    }

    public function testLongComment(): void
    {
        $comment = new Comment();
        $comment->setContent(str_repeat('a', 1001));

        $this->assertTrue(
            $this->spamChecker->isSpam($comment, '127.0.0.1'),
            'Un commentaire de plus de 1000 caractères devrait être détecté comme spam'
        );
    }

    public function testValidComment(): void
    {
        $comment = new Comment();
        $comment->setContent('Un commentaire tout à fait normal et approprié.');

        $this->assertFalse(
            $this->spamChecker->isSpam($comment, '127.0.0.1'),
            'Un commentaire valide ne devrait pas être détecté comme spam'
        );
    }
}
