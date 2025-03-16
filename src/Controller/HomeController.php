<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\SearchType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\MongoDBService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly MongoDBService $mongoDBService
    ) {}

    // Route définissant la page d'accueil ('/') avec le nom 'app_home'
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        // Enregistrer la visite dans MongoDB
        $this->mongoDBService->insertVisit('home_page');
        
        // Récupération du paramètre de recherche depuis la barre de navigation
        $navbarQuery = $request->query->get('q');
        
        // Si un terme de recherche est fourni via la navbar, rediriger vers la page de recherche
        if ($navbarQuery !== null) {
            return $this->redirectToRoute('app_search', ['q' => $navbarQuery]);
        }
        
        // Création du formulaire de recherche
        $searchForm = $this->createForm(SearchType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false,
            'action' => $this->generateUrl('app_search')
        ]);
        
        // Récupère les 4 derniers articles pour le carrousel
        $featuredArticles = $this->articleRepository->findLatest(4);
        
        // Récupère les 6 articles suivants pour la section "Articles récents"
        $latestArticles = $this->articleRepository->findLatestExcept(6, array_map(fn($article) => $article->getId(), $featuredArticles));

        // Récupérer toutes les catégories avec leurs articles associés
        $categories = $this->categoryRepository->findAllWithArticles();

        // Rendu de la vue Twig avec les données récupérées
        return $this->render('home/index.html.twig', [
            'latest_articles' => $latestArticles, // Les articles récents (hors articles en vedette)
            'categories' => $categories, // Les catégories avec leurs articles
            'featured_articles' => $featuredArticles, // Les articles en vedette pour le carrousel
            'featured_article' => !empty($featuredArticles) ? $featuredArticles[0] : null, // Pour compatibilité
            'search_form' => $searchForm->createView(), // Le formulaire de recherche
            'search_results' => [], // Pas de résultats de recherche sur la page d'accueil
        ]);
    }
}
