<?php

namespace App\Controller\Api;

use App\Dto\ModerateCommentRequest;
use App\Entity\Comment;
use App\Service\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\CommentRepository;

#[Route('/api/comments')]
#[IsGranted('ROLE_ADMIN')]
class CommentController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function getComments(CommentRepository $commentRepository): JsonResponse
    {
        $comments = $commentRepository->findAll();
        
        // Configuration de la sérialisation pour éviter les erreurs de propriétés circulaires
        return $this->json($comments, 200, [], [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            },
            'ignored_attributes' => [
                'updatedAtValue', 
                'article.updatedAtValue', 
                'author.articles', 
                'author.comments',
                'category',
                'article.category'
            ]
        ]);
    }

    public function getCommentbyId(CommentRepository $commentRepository, int $id): JsonResponse
    {
        $comment = $commentRepository->find($id);
        
        return $this->json($comment);
    }

    // public function createComment(Request $request, EntityManagerInterface $entityManager): JsonResponse
    // {
    //     $comment = new Comment();
    //     // $comment->setContent($request->request->get('content'));
    //     // $entityManager->persist($comment);
    //     // $entityManager->flush();
        
    //     return $this->json($comment, Response::HTTP_CREATED);
    // }

}
