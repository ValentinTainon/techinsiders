<?php

namespace App\Config;

use App\Enum\MimeType;
use App\Service\ImageConfigurator;

abstract class PostThumbnailConfig
{
    private const string BASE_PATH = '/images/uploads/post/';
    private const string DEFAULT_FILE_NAME = 'thumbnail.svg';
    private const int MAX_FILE_SIZE = 400;
    private const array ALLOWED_MIME_TYPES = [
        MimeType::JPEG,
        MimeType::PNG,
        MimeType::WEBP,
    ];

    public static function getConfig(string $uuid): ImageConfigurator
    {
        return new ImageConfigurator(
            self::BASE_PATH . "{$uuid}/thumbnail/",
            self::DEFAULT_FILE_NAME,
            self::MAX_FILE_SIZE,
            self::ALLOWED_MIME_TYPES
        );
    }
}
