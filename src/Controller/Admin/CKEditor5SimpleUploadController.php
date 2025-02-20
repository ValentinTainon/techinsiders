<?php

namespace App\Controller\Admin;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class CKEditor5SimpleUploadController extends AbstractController
{
    #[Route('/ckeditor5-simple-upload', name: 'ckeditor5_simple_upload', methods: ['POST'])]
    public function handleCkeditor5SimpleUpload(Request $request, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('upload');

        if (!$file) {
            return new JsonResponse(['error' => ['message' => 'No file uploaded']], 400);
        }

        $uploadDir = $request->headers->get('Upload-Directory');

        if (!$uploadDir) {
            return new JsonResponse(['error' => 'Invalid upload directory'], 400);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move(
                "{$this->getParameter('kernel.project_dir')}/public/{$uploadDir}",
                $newFilename
            );

            return new JsonResponse([
                'url' => "/{$uploadDir}/{$newFilename}"
            ]);
        } catch (FileException $e) {
            return new JsonResponse(['error' => ['message' => $e->getMessage()]], 500);
        }
    }

    #[Route('/ckeditor5-simple-upload-cleaner', name: 'ckeditor5_simple_upload_cleaner', methods: ['POST'])]
    public function handleCkeditor5SimpleUploadCleaner(Request $request): JsonResponse
    {
        $requestPayload = $request->toArray();
        $uploadDir = $requestPayload['uploadDir'];

        if (!$uploadDir) {
            return new JsonResponse(['error' => 'Invalid upload directory'], 500);
        }

        $filesystem = new Filesystem();
        $appUploadDir = "{$this->getParameter('kernel.project_dir')}/public/{$uploadDir}";

        if (!$filesystem->exists($appUploadDir)) {
            return new JsonResponse(['status' => 'No upload directory'], 200);
        }

        try {
            $finder = new Finder();
            $finder->files()->in($appUploadDir);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['status' => 'No files in this upload directory'], 200);
        }

        if (!$finder->hasResults()) {
            $filesystem->remove($appUploadDir);
            return new JsonResponse(['status' => 'Remove empty upload directory'], 200);
        }

        $imagesInAppUploadDir = [];

        foreach ($finder as $file) {
            $imagesInAppUploadDir[] = $file->getFilename();
        }

        $imagesToDelete = array_diff($imagesInAppUploadDir, $requestPayload['editorImages']);

        if (empty($imagesToDelete)) {
            return new JsonResponse(['status' => 'No images to delete'], 200);
        }

        foreach ($imagesToDelete as $img) {
            if ($filesystem->exists("{$appUploadDir}/{$img}")) {
                $filesystem->remove("{$appUploadDir}/{$img}");
            }
        }

        $finder = new Finder();
        $finder->files()->in($appUploadDir);

        if (!$finder->hasResults()) {
            $filesystem->remove($appUploadDir);
        }

        return new JsonResponse(['status' => 'Cleanup complete'], 200);
    }
}
