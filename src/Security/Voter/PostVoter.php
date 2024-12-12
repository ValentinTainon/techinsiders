<?php

namespace App\Security\Voter;

use App\Entity\Post;
use App\Enum\UserRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class PostVoter extends Voter
{
    public const string EDIT = 'ADMIN_POST_EDIT';
    public const string DELETE = 'ADMIN_POST_DELETE';
    public const string BATCH_DELETE = 'ADMIN_POST_BATCH_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array(
            $attribute,
            [self::EDIT, self::DELETE, self::BATCH_DELETE]
        ) && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        $post = &$subject;

        return match ($attribute) {
            self::EDIT,
            self::DELETE,
            self::BATCH_DELETE => $user === $post->getUser() ||
                $this->security->isGranted(UserRole::SUPER_ADMIN->value),
            default => false,
        };
    }
}
