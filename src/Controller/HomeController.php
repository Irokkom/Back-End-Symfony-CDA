<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\MongoDBService;
use App\Service\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly SearchService $searchService,
        private readonly MongoDBService $mongoDBService
    ) {}

    // Route définissant la page d'accueil ('/') avec le nom 'app_home'
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        // Enregistrer la visite dans MongoDB
        $this->mongoDBService->insertVisit('home_page');
        
        //  Utiliser le service de recherche pour gérer la soumission du formulaire de recherche
        $search = $this->searchService->handleSearchForm($request, 'app_home');
    
        // Récupère les résultats de la recherche (si disponibles)
        $searchResults = $search['results'];
    
        // Récupère les 6 derniers articles pour le carrousel
        $featuredArticles = $this->articleRepository->findLatest(6);
        
        // Récupère les 6 articles suivants pour la section "Articles récents"
        $latestArticles = empty($searchResults) 
            ? $this->articleRepository->findLatestExcept(6, array_map(fn($article) => $article->getId(), $featuredArticles)) 
            : [];

        //  Récupérer toutes les catégories avec leurs articles associés
        $categories = $this->categoryRepository->findAllWithArticles();

        //  Rendu de la vue Twig avec les données récupérées
        return $this->render('home/index.html.twig', [
            'latest_articles' => $latestArticles, // Les articles récents (hors articles en vedette)
            'categories' => $categories, // Les catégories avec leurs articles
            'featured_articles' => $featuredArticles, // Les articles en vedette pour le carrousel
            'featured_article' => !empty($featuredArticles) ? $featuredArticles[0] : null, // Pour compatibilité
            'search_form' => $search['form']->createView(), // Le formulaire de recherche
            'search_results' => $searchResults, // Les résultats de la recherche
        ]);
    }

}
