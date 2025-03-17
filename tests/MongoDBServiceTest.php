<?php

namespace App\Tests\Service;

use App\Service\MongoDBService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

// Test du service MongoDB
class MongoDBServiceTest extends TestCase
{
    private MongoDBService $mongoDBService;
    private $httpClient;

    protected function setUp(): void
    {
        // Mock du client HTTP
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        
        // Création du service avec le mock
        $this->mongoDBService = new MongoDBService($this->httpClient);
    }

    public function testInsertVisit(): void
    {
        // Mock de la réponse
        $response = $this->createMock(ResponseInterface::class);
        
        // Configuration du client HTTP
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://us-east-2.aws.neurelo.com/rest/visits/__one',
                $this->callback(function ($options) {
                    // Vérification des données envoyées
                    return isset($options['headers']['X-API-KEY']) &&
                           isset($options['json']['pageName']) &&
                           $options['json']['pageName'] === 'test_page';
                })
            )
            ->willReturn($response);
        
        // Exécution
        $this->mongoDBService->insertVisit('test_page');
        
        // Test réussi si on arrive ici
        $this->assertTrue(true);
    }
}
