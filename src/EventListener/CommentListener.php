<?php

namespace App\EventListener;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AsEntityListener(event: Events::prePersist, entity: Comment::class)]
class CommentListener
{
    public function __construct(
        private Security $security
    ) {}

    public function prePersist(Comment $comment, LifecycleEventArgs $event): void
    {
        // Définit la date de création
        if ($comment->getCreatedAt() === null) {
            $comment->setCreatedAt(new \DateTime());
        }

        // Vérifie que l'auteur est bien l'utilisateur connecté
        $user = $this->security->getUser();
        if (!$user || $comment->getCommentAuthor() !== $user->getUsername()) {
            throw new AccessDeniedException('Vous ne pouvez pas créer un commentaire pour un autre utilisateur.');
        }
    }
}
