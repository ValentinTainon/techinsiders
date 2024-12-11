<?php

namespace App\Service;

class ImageConfigurator
{
    private const string DEFAULT_BASE_PATH = 'images/default/';

    private string $basePath;
    private string $defaultFileName;
    private int $maxFileSize;
    private array $allowedMimeTypes;

    public function __construct(
        string $basePath,
        string $defaultFileName,
        int $maxFileSize,
        array $allowedMimeTypes
    ) {
        $this->basePath = $basePath;
        $this->defaultFileName = $defaultFileName;
        $this->maxFileSize = $maxFileSize;
        $this->allowedMimeTypes = $allowedMimeTypes;
    }

    public function defaultFilePath(): string
    {
        return sprintf('%s%s', self::DEFAULT_BASE_PATH, $this->defaultFileName);
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function uploadDir(): string
    {
        return sprintf('/public%s', $this->basePath);
    }

    public function imgPath(string $fileName): string
    {
        return sprintf('%s%s', $this->basePath, $fileName);
    }

    public function defaultFileName(): string
    {
        return $this->defaultFileName;
    }

    public function maxFileSize(): string
    {
        return sprintf('%dk', $this->maxFileSize);
    }

    public function allowedMimeTypes(): array
    {
        return array_map(
            fn($mimeType) => $mimeType->value,
            $this->allowedMimeTypes
        );
    }

    public function allowedMimeTypesExtensions(): string
    {
        return implode(
            ', ',
            array_merge(
                ...array_map(
                    fn($mimeType) => $mimeType->extensions(),
                    $this->allowedMimeTypes
                )
            )
        );
    }
}
