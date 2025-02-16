<?php

namespace App\Config;

use App\Enum\MimeType;
use App\Service\ImageConfigurator;

abstract class UserAvatarConfig
{
    public const string BASE_PATH = '/images/uploads/user/avatar/';
    public const string DEFAULT_FILE_NAME = 'avatar.svg';
    public const int MAX_FILE_SIZE = 500;
    public const array ALLOWED_MIME_TYPES = [
        MimeType::JPEG,
        MimeType::PNG,
        MimeType::WEBP,
        MimeType::SVG,
        MimeType::GIF,
    ];

    public static function getConfig(): ImageConfigurator
    {
        return new ImageConfigurator(
            self::BASE_PATH,
            self::DEFAULT_FILE_NAME,
            self::MAX_FILE_SIZE,
            self::ALLOWED_MIME_TYPES
        );
    }
}
