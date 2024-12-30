<?php

namespace App\Config;

abstract class AppConfig
{
    public const string APP_VERSION = '1.0.0';
    public const string APP_NAME = 'TechInsiders';
    public const string APP_DESCRIPTION = 'TechInsiders is a blog where you can find the latest news about technology.';
    public const string APP_BASE_URL = "https://techinsiders.xyz"; // SYMFONY_DEFAULT_ROUTE_URL(server)
    public const string APP_CONTACT_EMAIL = 'contact@techinsiders.xyz';
    public const string APP_AUTHOR = 'Val';
    public const string GITHUB_URL = 'https://github.com/ValentinTainon/techinsiders';
}
