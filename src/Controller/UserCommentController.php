<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserCommentController extends AbstractController
{
    #[Route('/mes-commentaires', name: 'app_user_comments')]
    #[IsGranted('ROLE_USER')]
    public function index(CommentRepository $commentRepository): Response
    {
        $user = $this->getUser();
        
        // Récupérer tous les commentaires de l'utilisateur, quel que soit le statut
        $userComments = $commentRepository->createQueryBuilder('c')
            ->andWhere('c.author = :user')
            ->setParameter('user', $user)
            ->andWhere('c.deletedAt IS NULL')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('user_comment/index.html.twig', [
            'user_comments' => $userComments,
        ]);
    }
}
