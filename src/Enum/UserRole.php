<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatorInterface;

enum UserRole: string
{
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case ADMIN = 'ROLE_ADMIN';
    case EDITOR = 'ROLE_EDITOR';
    case USER = 'ROLE_USER';
    case GUEST = 'ROLE_GUEST';

    public function label(TranslatorInterface $translator): string
    {
        return match ($this) {
            self::SUPER_ADMIN => $translator->trans('role.super_admin.label', [], 'forms'),
            self::ADMIN => $translator->trans('role.admin.label', [], 'forms'),
            self::EDITOR => $translator->trans('role.editor.label', [], 'forms'),
            self::USER => $translator->trans('role.user.label', [], 'forms'),
            self::GUEST => $translator->trans('role.guest.label', [], 'forms'),
        };
    }
}
