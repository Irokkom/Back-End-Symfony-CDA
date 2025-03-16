<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\SearchType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/recherche', name: 'app_search')]
    public function index(Request $request, ArticleRepository $articleRepository): Response
    {
        // Récupération des paramètres de recherche depuis la requête
        $navbarQuery = $request->query->get('q');
        
        // Préparation des données par défaut pour le formulaire
        $defaultData = [
            'query' => $navbarQuery ?? $request->query->get('query'),
            'category' => $request->query->get('category'),
            'dateFrom' => $request->query->get('dateFrom') ? new \DateTime($request->query->get('dateFrom')) : null,
            'dateTo' => $request->query->get('dateTo') ? new \DateTime($request->query->get('dateTo')) : null,
        ];
        
        // Création du formulaire de recherche
        $form = $this->createForm(SearchType::class, $defaultData, [
            'method' => 'GET',
            'csrf_protection' => false
        ]);
        $form->handleRequest($request);
        
        // Initialisation des résultats
        $results = [];
        $totalResults = 0;
        
        // Récupération des données de recherche
        $searchData = $form->getData() ?: $defaultData;
        
        // Filtrage des valeurs non vides
        $searchData = array_filter($searchData, function($value) {
            return $value !== null && $value !== '';
        });
        
        // Exécution de la recherche si des critères sont spécifiés
        if (!empty($searchData)) {
            // Préparation des filtres pour le repository
            $filters = [];
            
            if (isset($searchData['query'])) {
                $filters['query'] = $searchData['query'];
            }
            
            if (isset($searchData['category'])) {
                $filters['category'] = $searchData['category'];
            }
            
            if (isset($searchData['dateFrom'])) {
                $filters['dateFrom'] = $searchData['dateFrom'];
            }
            
            if (isset($searchData['dateTo'])) {
                $filters['dateTo'] = $searchData['dateTo'];
            }
            
            // Débogage - Afficher les filtres
            // dd($filters);
            
            // Appel direct à la méthode du repository
            $results = $articleRepository->findByAdvancedFilters($filters);
            $totalResults = count($results);
        }
        
        return $this->render('search/index.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
            'total_results' => $totalResults,
            'search_query' => $searchData['query'] ?? ''
        ]);
    }
}
