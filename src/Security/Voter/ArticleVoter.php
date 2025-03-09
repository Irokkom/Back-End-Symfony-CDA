<?php

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    public function __construct(
        private readonly Security $security
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Article;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Les admins peuvent tout faire
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var Article $article */
        $article = $subject;

        return match($attribute) {
            self::EDIT, self::DELETE => $this->canEditOrDelete($article, $user),
            default => false,
        };
    }

    private function canEditOrDelete(Article $article, UserInterface $user): bool
    {
        return $article->getAuthor() === $user;
    }
}
