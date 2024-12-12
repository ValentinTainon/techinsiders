<?php

namespace App\Security\Voter;

use App\Enum\UserRole;
use App\Entity\Comment;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class CommentVoter extends Voter
{
    public const string EDIT = 'ADMIN_COMMENT_EDIT';
    public const string DETAIL = 'ADMIN_COMMENT_VIEW';
    public const string DELETE = 'ADMIN_COMMENT_DELETE';
    public const string BATCH_DELETE = 'ADMIN_COMMENT_BATCH_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array(
            $attribute,
            [self::EDIT, self::DETAIL, self::DELETE, self::BATCH_DELETE]
        ) && $subject instanceof Comment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        $comment = &$subject;

        return match ($attribute) {
            self::EDIT,
            self::DETAIL,
            self::DELETE,
            self::BATCH_DELETE => $user === $comment->getUser() ||
                $this->security->isGranted(UserRole::SUPER_ADMIN->value),
            default => false,
        };
    }
}
