<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\SearchService;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly SearchService $searchService
    ) {}

    #[Route('/articles', name: 'app_article_index')]
    public function index(Request $request): Response
    {
        // Utiliser le service de recherche
        $search = $this->searchService->handleSearchForm($request, 'app_article_index');
        
        // Si des résultats de recherche sont trouvés, les utiliser, sinon afficher tous les articles actifs
        $articles = !empty($search['results']) ? $search['results'] : $this->articleRepository->findAllActive();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $this->categoryRepository->findAll(),
            'search_form' => $search['form']->createView()
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
