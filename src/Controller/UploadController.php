<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Service\PathService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use function PHPUnit\Framework\isNull;

class UploadController extends AbstractController
{
    #[Route('/upload-post-image', name: 'upload_post_image', methods: ['POST'])]
    public function uploadPostImage(Request $request, PostRepository $postRepository, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('upload');

        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], 400);
        }

        $currentPostId = $request->query->get('current-post-id');

        if ($currentPostId <= 0) {
            return new JsonResponse(['error' => 'Invalid current post id'], 400);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move(
                $this->getParameter('kernel.project_dir') . PathService::POSTS_IMAGES_UPLOAD_DIR . $currentPostId,
                $newFilename
            );

            return new JsonResponse([
                'url' => PathService::POSTS_IMAGES_BASE_PATH . $currentPostId . '/' . $newFilename
            ]);
        } catch (FileException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
