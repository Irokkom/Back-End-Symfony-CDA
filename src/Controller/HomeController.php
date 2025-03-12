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
    
        //  Si aucun résultat de recherche, on récupère les 6 derniers articles publiés
        $latestArticles = empty($searchResults) 
            ? $this->articleRepository->findLatest(6) 
            : [];

        //  Récupérer toutes les catégories avec leurs articles associés
        $categories = $this->categoryRepository->findAllWithArticles();

        //  Récupérer l'article le plus récent pour l'afficher en "article en avant"
        $featuredArticle = !empty($latestArticles) ? $latestArticles[0] : null;
    
        //  Si un article en avant est défini, on le retire de la liste des derniers articles
        if ($featuredArticle) {
            array_shift($latestArticles); // Retire le premier élément du tableau
        }

        //  Rendu de la vue Twig avec les données récupérées
        return $this->render('home/index.html.twig', [
            'latest_articles' => $latestArticles, // Les articles récents (hors article en avant)
            'categories' => $categories, // Les catégories avec leurs articles
            'featured_article' => $featuredArticle, // L'article en avant
            'search_form' => $search['form']->createView(), // Le formulaire de recherche
            'search_results' => $searchResults, // Les résultats de la recherche
        ]);
    }

}
