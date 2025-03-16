<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\SearchType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/articles', name: 'app_article_index')]
    public function index(Request $request): Response
    {
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
        
        // Récupérer tous les articles actifs
        $articles = $this->articleRepository->findAllActive();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $this->categoryRepository->findAll(),
            'search_form' => $searchForm->createView()
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_show')]
    public function show(Article $article, Request $request): Response
    {
        if ($article->getDeletedAt() !== null) {
            throw $this->createNotFoundException('Cet article n\'existe pas ou a été supprimé');
        }

        // Création du formulaire de commentaire
        $comment = new Comment();
        $comment->setArticle($article);
        
        // Si l'utilisateur est connecté, associer le commentaire à l'utilisateur
        if ($this->getUser()) {
            $user = $this->getUser();
            $comment->setAuthor($user);
        }
        
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Définir le statut du commentaire (par défaut en attente)
            $comment->setStatus('pending');
            
            // Persister le commentaire
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Votre commentaire a été ajouté et sera visible après modération.');
            
            // Rediriger vers la même page pour éviter la soumission multiple du formulaire
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'comment_form' => $form->createView()
        ]);
    }

    #[Route('/category', name: 'app_article_category_index')]
    public function categoryIndex(): Response
    {
        // Rediriger vers la liste des articles
        return $this->redirectToRoute('app_article_index');
    }

    #[Route('/category/{name}', name: 'app_article_by_category')]
    public function byCategory(string $name): Response
    {
        // Ajout de logs pour déboguer
        error_log("Tentative d'accès à la catégorie: " . $name);
        
        // Recherche insensible à la casse
        $categories = $this->categoryRepository->findAll();
        $category = null;
        
        foreach ($categories as $cat) {
            if (strtolower($cat->getName()) === strtolower($name)) {
                $category = $cat;
                break;
            }
        }
        
        if (!$category) {
            error_log("Catégorie non trouvée: " . $name);
            // Au lieu de lancer une exception, on rend une page d'erreur personnalisée
            return $this->render('article/category_not_found.html.twig', [
                'category_name' => $name,
                'categories' => $categories,
            ], new Response('', Response::HTTP_NOT_FOUND));
        }
        
        error_log("Catégorie trouvée: " . $category->getName() . " (ID: " . $category->getId() . ")");
        
        // Utiliser le nom exact de la catégorie trouvée pour la recherche d'articles
        $articles = $this->articleRepository->findByCategory($category->getName());
        error_log("Nombre d'articles trouvés: " . count($articles));
        
        return $this->render('article/by_category.html.twig', [
            'category' => $category,
            'articles' => $articles,
            'categories' => $categories,
            'category_name' => $category->getName()
        ]);
    }
}
