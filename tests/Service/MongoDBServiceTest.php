<?php

namespace App\Tests\Service;

use App\Service\MongoDBService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MongoDBServiceTest extends TestCase
{
    private MongoDBService $mongoDBService;
    private $httpClient;

    protected function setUp(): void
    {
        // Création d'un mock pour HttpClientInterface
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        
        // Création de l'instance de MongoDBService avec le mock
        $this->mongoDBService = new MongoDBService($this->httpClient);
    }

    public function testInsertVisit(): void
    {
        // Création d'un mock pour ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        
        // Configuration du mock HttpClient pour retourner le mock Response
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://us-east-2.aws.neurelo.com/rest/visits/__one',
                $this->callback(function ($options) {
                    // Vérification que les headers contiennent l'API key
                    $this->assertArrayHasKey('headers', $options);
                    $this->assertArrayHasKey('X-API-KEY', $options['headers']);
                    $this->assertArrayHasKey('Content-Type', $options['headers']);
                    
                    // Vérification que le JSON contient les bonnes clés
                    $this->assertArrayHasKey('json', $options);
                    $this->assertArrayHasKey('pageName', $options['json']);
                    $this->assertArrayHasKey('visitedAt', $options['json']);
                    
                    // Vérification que le pageName est correct
                    $this->assertEquals('test_page', $options['json']['pageName']);
                    
                    return true;
                })
            )
            ->willReturn($response);
        
        // Appel de la méthode à tester
        $this->mongoDBService->insertVisit('test_page');
        
        // Test réussi si on arrive ici sans erreur
        $this->assertTrue(true);
    }
}
