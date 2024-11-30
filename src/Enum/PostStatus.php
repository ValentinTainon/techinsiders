<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatorInterface;

enum PostStatus: string
{
    case DRAFTED = 'DRAFTED';
    case READY_FOR_REVIEW = 'READY_FOR_REVIEW';
    case PUBLISHED = 'PUBLISHED';
    case REJECTED = 'REJECTED';

    public function label(TranslatorInterface $translator): string
    {
        return match ($this) {
            self::DRAFTED => $translator->trans('status.draft.label', [], 'forms'),
            self::READY_FOR_REVIEW => $translator->trans('status.ready_for_review.label', [], 'forms'),
            self::PUBLISHED => $translator->trans('status.published.label', [], 'forms'),
            self::REJECTED => $translator->trans('status.rejected.label', [], 'forms'),
        };
    }
}
