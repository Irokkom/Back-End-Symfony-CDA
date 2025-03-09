<?php

namespace App\Service;

use App\Entity\Comment;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class SpamChecker
{
    private const SPAM_KEYWORDS = [
        'viagra', 'casino', 'xxx', 'buy now', 'free money',
        'make money fast', 'work from home', 'earn extra cash',
        'winner', 'lottery', 'prize', 'congratulations'
    ];

    private const CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private readonly RateLimiterFactory $commentLimiter,
        private readonly CacheItemPoolInterface $cache
    ) {}

    public function isSpam(Comment $comment): bool
    {
        // Check cache first
        $cacheKey = 'spam_check_' . $comment->getId();
        $cacheItem = $this->cache->getItem($cacheKey);
        
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $isSpam = $this->checkSpam($comment);
        
        // Cache the result
        $cacheItem->set($isSpam);
        $cacheItem->expiresAfter(self::CACHE_TTL);
        $this->cache->save($cacheItem);

        return $isSpam;
    }

    private function checkSpam(Comment $comment): bool
    {
        $text = strtolower($comment->getContent());
        
        // Check content length
        if (strlen($text) > 1000) {
            return true;
        }

        // Check for spam keywords
        foreach (self::SPAM_KEYWORDS as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        // Check for repeated characters (potential spam)
        if (preg_match('/(.)\1{4,}/', $text)) {
            return true;
        }

        // Check for too many URLs (potential spam)
        if (substr_count($text, 'http') > 3) {
            return true;
        }

        return false;
    }
}
