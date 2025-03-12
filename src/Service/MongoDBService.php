<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use DateTimeImmutable;

class   MongoDBService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function insertVisit(string $pageName) 
    {
        $this->httpClient->request('POST', 'https://us-east-2.aws.neurelo.com/rest/visits/__one', [
            'headers' => [
                'X-API-KEY' => 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImFybjphd3M6a21zOnVzLWVhc3QtMjowMzczODQxMTc5ODQ6YWxpYXMvYjJjYWNlYWItQXV0aC1LZXkifQ.eyJlbnZpcm9ubWVudF9pZCI6IjJhZGFlNWEyLTI3ZDAtNDNjOS04OGU1LTM0NTI0ZWQ1YjViYyIsImdhdGV3YXlfaWQiOiJnd19iMmNhY2VhYi0yYTRlLTQ3YzYtOTlkZS1iNDM3M2I4NWE2MjIiLCJwb2xpY2llcyI6WyJSRUFEIiwiV1JJVEUiLCJVUERBVEUiLCJERUxFVEUiLCJDVVNUT00iXSwiaWF0IjoiMjAyNS0wMy0xMlQwMjoyNzowOS42MDQxMzE4MThaIiwianRpIjoiYTA4MTg0N2MtYThjNS00MjE5LWJiMjMtNGM2NDQ5ZDczMzAwIn0.VnE4m6duRj1-PAWjlIm52N2_9LphPXnNmcwQPUVQDVg53Sj39XHazLtVs-T0s-nWaVM_c8155dN4881qxPzkzfvzNFRL_2P_N4p8SWVm8oS1b2McwAPlQYmru-5qveVlFmdQQtW5eLBJmpz4dvDdVn1wdiIVZNSu5ZTpVrAZCr56JMOZNnlnF9BHFjXk26qWHWNEvmk8PU3G1fQHGVTUM28bCmpd0X7xqiLvmGWpjlSNXebEV1hH_QVRRbOJrifapUWSHbwfy4wzzNrUD_CWRlS5FhAe8gjYANp0iWugM2zTentkzGtOKMgUf8KDsEiOAZa_a9irwxm1oLKfyXLapQ',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'pageName' => $pageName,
                'visitedAt' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}