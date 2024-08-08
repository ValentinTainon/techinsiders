<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UploadController extends AbstractController
{
    #[Route('/upload')]
    public function upload(Request $request, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('upload');
        $uploadPostsImagesDir = $this->getParameter('uploads_posts_images_directory');

        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move($uploadPostsImagesDir, $newFilename);
                return new JsonResponse([
                    'url' => '/uploads/posts/images/'.(string)$newFilename
                ]);
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Unable to upload file'], 500);
            }
        }

        return new JsonResponse(['error' => 'No file uploaded'], 400);
    }
}
