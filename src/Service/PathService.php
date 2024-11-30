<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Post;

class PathService
{
    /* Default images path */
    public const string DEFAULT_IMAGES_BASE_PATH = 'images/default/';

    /* Users avatars paths */
    public const string USERS_AVATAR_BASE_PATH = '/uploads/images/users/avatars/';
    public const string USERS_AVATAR_UPLOAD_DIR = '/public' . self::USERS_AVATAR_BASE_PATH;

    /* Posts thumbnails paths */
    public const string POSTS_THUMBNAIL_BASE_PATH = '/uploads/images/posts/thumbnails/';
    public const string POSTS_THUMBNAIL_UPLOAD_DIR = '/public' . self::POSTS_THUMBNAIL_BASE_PATH;

    /* Posts contents paths */
    public const string POSTS_IMAGES_BASE_PATH = '/uploads/images/posts/contents/';
    public const string POSTS_IMAGES_UPLOAD_DIR = '/public' . self::POSTS_IMAGES_BASE_PATH;

    /* Email templates path */
    public const string EMAIL_TEMPLATES_ADMIN_DIR = 'emails/admin/';
    public const string EMAIL_TEMPLATES_USER_DIR = 'emails/user/';


    public function userAvatarFullBasePath(string $avatar): string
    {
        return $avatar === null
            ? self::DEFAULT_IMAGES_BASE_PATH . User::DEFAULT_AVATAR_FILE_NAME
            : self::USERS_AVATAR_BASE_PATH . $avatar;
    }

    public function postThumbnailFullBasePath(string $thumbnail): string
    {
        return $thumbnail === null
            ? self::DEFAULT_IMAGES_BASE_PATH . Post::DEFAULT_THUMBNAIL_FILE_NAME
            : self::POSTS_THUMBNAIL_BASE_PATH . $thumbnail;
    }

    public function getAdminEmailTemplate(string $template): string
    {
        return self::EMAIL_TEMPLATES_ADMIN_DIR . $template;
    }

    public function getUserEmailTemplate(string $template): string
    {
        return self::EMAIL_TEMPLATES_USER_DIR . $template;
    }
}
