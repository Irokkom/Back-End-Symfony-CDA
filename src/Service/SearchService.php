<?php

namespace App\Service;

use App\Form\SearchType;
use App\Repository\ArticleRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchService
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

    /**
     * Crée et traite un formulaire de recherche
     * 
     * @param Request $request La requête HTTP
     * @param string $routeName Le nom de la route pour l'action du formulaire
     * @return array Un tableau contenant le formulaire et les résultats de la recherche
     */
    public function handleSearchForm(Request $request, string $routeName): array
    {
        // Créer le formulaire de recherche
        $searchForm = $this->formFactory->create(SearchType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false,
            'action' => $this->urlGenerator->generate($routeName)
        ]);

        $searchForm->handleRequest($request);
        $searchResults = [];
        $filters = [];

        // Vérifier s'il y a une requête de recherche directe depuis la navbar
        $query = $request->query->get('query');
        if (!empty($query)) {
            $filters['query'] = $query;
        } 
        // Sinon, vérifier si le formulaire de recherche a été soumis
        elseif ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();
            
            // Ajouter les filtres basés sur les données du formulaire
            if (!empty($data['query'])) {
                $filters['query'] = $data['query'];
            }
            
            if (!empty($data['category'])) {
                $filters['category'] = $data['category'];
            }
            
            if (!empty($data['dateFrom'])) {
                $filters['dateFrom'] = $data['dateFrom'];
            }
            
            if (!empty($data['dateTo'])) {
                $filters['dateTo'] = $data['dateTo'];
            }
        }
        
        // Si nous avons des filtres, utiliser la recherche avancée
        if (!empty($filters)) {
            $searchResults = $this->articleRepository->findByAdvancedFilters($filters);
        }

        return [
            'form' => $searchForm,
            'results' => $searchResults
        ];
    }
}
