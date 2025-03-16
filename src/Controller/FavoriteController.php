<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FavoriteController extends AbstractController
{
    private const MSG_ADDED = 'Article ajouté aux favoris';
    private const MSG_REMOVED = 'Article retiré des favoris';

    #[Route('/favoris', name: 'app_favorites')]
    #[IsGranted('ROLE_USER')]
    public function index(ArticleRepository $articleRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $favoriteArticles = $user->getFavoriteArticles();

        return $this->render('favorite/index.html.twig', [
            'favorite_articles' => $favoriteArticles,
        ]);
    }

    #[Route('/article/{id}/favori/ajouter', name: 'app_favorite_add')]
    #[IsGranted('ROLE_USER')]
    public function addFavorite(Article $article, EntityManagerInterface $entityManager, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user->isFavorite($article)) {
            $user->addFavoriteArticle($article);
            $entityManager->flush();
            $this->addFlash('success', self::MSG_ADDED);
        }
        
        // Si c'est une requête AJAX, retourner une réponse JSON
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'isFavorite' => true,
                'message' => self::MSG_ADDED
            ]);
        }
        
        // Rediriger vers la page précédente
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_article_index'));
    }

    #[Route('/article/{id}/favori/supprimer', name: 'app_favorite_remove')]
    #[IsGranted('ROLE_USER')]
    public function removeFavorite(Article $article, EntityManagerInterface $entityManager, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($user->isFavorite($article)) {
            $user->removeFavoriteArticle($article);
            $entityManager->flush();
            $this->addFlash('success', self::MSG_REMOVED);
        }
        
        // Si c'est une requête AJAX, retourner une réponse JSON
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'isFavorite' => false,
                'message' => self::MSG_REMOVED
            ]);
        }
        
        // Rediriger vers la page précédente
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_favorites'));
    }

    #[Route('/article/{id}/favori/toggle', name: 'app_favorite_toggle')]
    #[IsGranted('ROLE_USER')]
    public function toggleFavorite(Article $article, EntityManagerInterface $entityManager, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $isFavorite = $user->isFavorite($article);
        
        if ($isFavorite) {
            $user->removeFavoriteArticle($article);
            $message = self::MSG_REMOVED;
        } else {
            $user->addFavoriteArticle($article);
            $message = self::MSG_ADDED;
        }
        
        $entityManager->flush();
        $this->addFlash('success', $message);
        
        // Si c'est une requête AJAX, retourner une réponse JSON
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'isFavorite' => !$isFavorite,
                'message' => $message
            ]);
        }
        
        // Rediriger vers la page précédente
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_article_index'));
    }
}
