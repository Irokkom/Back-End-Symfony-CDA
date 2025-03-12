<?php

namespace App\Controller\Api;

use App\Dto\UpdateCommentDto;
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
        
        return $this->json($comments, 200, [], ['groups' => 'comment:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getCommentbyId(CommentRepository $commentRepository, int $id): JsonResponse
    {
        $comment = $commentRepository->find($id);
        
        return $this->json($comment, 200, [], ['groups' => 'comment:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function updateComment(CommentRepository $commentRepository, int $id, #[MapRequestPayload] UpdateCommentDto $updateCommentDto, EntityManagerInterface $entityManager): JsonResponse
    {
        $comment = $commentRepository->find($id);
        
        if (!$comment) {
            return $this->json(['error' => 'Comment not found'], 404);
        }
        $comment->setStatus($updateCommentDto->getStatus());
        $entityManager->persist($comment);
        $entityManager->flush();


        return $this->json($comment, 200, [], ['groups' => 'comment:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteComment(CommentRepository $commentRepository, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $comment = $commentRepository->find($id);
        if (!$comment) {
            return $this->json(['error' => 'Comment not found'], 404);
        }
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->json(['message' => 'Comment deleted successfully'], 200);
    }

}
    
