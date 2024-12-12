<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class UserVoter extends Voter
{
    public const string EDIT = 'ADMIN_USER_EDIT';
    public const string DETAIL = 'ADMIN_USER_VIEW';
    public const string DELETE = 'ADMIN_USER_DELETE';
    public const string BATCH_DELETE = 'ADMIN_USER_BATCH_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array(
            $attribute,
            [self::EDIT, self::DETAIL, self::DELETE, self::BATCH_DELETE]
        ) && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::EDIT,
            self::DETAIL,
            self::DELETE,
            self::BATCH_DELETE => $user === $subject ||
                $this->security->isGranted(UserRole::SUPER_ADMIN->value),
            default => false,
        };
    }
}
