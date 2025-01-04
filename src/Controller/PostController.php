<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Enum\PostStatus;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/post')]
final class PostController extends AbstractController
{
    #[Route(name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findBy(['status' => PostStatus::PUBLISHED], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/{nameSlug:category}/{titleSlug:post}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post, EntityManagerInterface $entityManager): Response
    {
        $this->incrementNumberOfViews($post);
        $entityManager->flush();

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    private function incrementNumberOfViews(Post $post): void
    {
        $post->setNumberOfViews($post->getNumberOfViews() + 1);
    }
}
