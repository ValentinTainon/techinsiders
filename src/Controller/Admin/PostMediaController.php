<?php

namespace App\Controller\Admin;

use function PHPUnit\Framework\throwException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostMediaController extends AbstractController
{
    #[Route('/upload-post-image', name: 'upload_post_image', methods: ['POST'])]
    public function uploadPostImage(Request $request, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('upload');

        if (!$file) {
            return new JsonResponse(['error' => ['message' => 'No file uploaded']], 400);
        }

        $postUuid = $request->headers->get('Post-Uuid');

        if (!$postUuid) {
            return new JsonResponse(['error' => 'Invalid Post Uuid'], 400);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move(
                "{$this->getParameter('kernel.project_dir')}/public/uploads/images/posts/contents/{$postUuid}",
                $newFilename
            );

            return new JsonResponse([
                'url' => "/uploads/images/posts/contents/{$postUuid}/{$newFilename}"
            ]);
        } catch (FileException $e) {
            return new JsonResponse(['error' => ['message' => $e->getMessage()]], 500);
        }
    }

    #[Route('/handle-deleted-post-images', name: 'handle_deleted_post_images', methods: ['POST'])]
    public function handleDeletedPostImages(Request $request): void
    {
        $postContentUploadDir = "{$this->getParameter('kernel.project_dir')}/public/uploads/images/posts/contents";

        try {
            $finder = new Finder();
            $finder->directories()->in($postContentUploadDir);
        } catch (\InvalidArgumentException $e) {
            throwException($e);
            return;
        }

        if (!$finder->hasResults()) return;

        $requestPayload = $request->toArray();
        $postUuid = $requestPayload['postUuid'];

        $filesystem = new Filesystem();
        $currentPostImgDir = "{$postContentUploadDir}/{$postUuid}";

        if (!$filesystem->exists($currentPostImgDir)) {
            return;
        }

        try {
            $finder = new Finder();
            $finder->files()->in($currentPostImgDir);
        } catch (\InvalidArgumentException $e) {
            throwException($e);
            return;
        }

        if (
            !$finder->hasResults() ||
            ($requestPayload['pageName'] === Crud::PAGE_NEW && $requestPayload['eventType'] === 'beforeunload')
        ) {
            if ($filesystem->exists($currentPostImgDir)) {
                $filesystem->remove($currentPostImgDir);
            }
            return;
        }

        $currentPostImgInApp = [];

        foreach ($finder as $fileInApp) {
            $currentPostImgInApp[] = Path::makeRelative(
                $fileInApp->getRealPath(),
                $this->getParameter('kernel.project_dir')
            );
        }

        $imagesToDelete = array_diff($currentPostImgInApp, $requestPayload['postImgPaths']);

        if (empty($imagesToDelete)) return;

        foreach ($imagesToDelete as $imagePath) {
            if ($filesystem->exists("{$this->getParameter('kernel.project_dir')}/{$imagePath}")) {
                $filesystem->remove("{$this->getParameter('kernel.project_dir')}/{$imagePath}");
            }
        }

        $finder = new Finder();
        $finder->files()->in($currentPostImgDir);

        if (!$finder->hasResults()) {
            $filesystem->remove($currentPostImgDir);
        }
    }
}
