<?php

namespace App\Enum;

use Symfony\Component\Mime\MimeTypes;

enum MimeType: string
{
    case JPEG = 'image/jpeg';
    case PNG = 'image/png';
    case WEBP = 'image/webp';
    case SVG = 'image/svg+xml';
    case GIF = 'image/gif';

    public function extensions(): array
    {
        $mimeTypes = new MimeTypes();

        return match ($this) {
            self::JPEG => $mimeTypes->getExtensions(self::JPEG->value),
            self::PNG => $mimeTypes->getExtensions(self::PNG->value),
            self::WEBP => $mimeTypes->getExtensions(self::WEBP->value),
            self::SVG => $mimeTypes->getExtensions(self::SVG->value),
            self::GIF => $mimeTypes->getExtensions(self::GIF->value),
        };
    }
}
