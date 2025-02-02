<?php

namespace App\Controller\Front;

use App\Entity\Tag;
use App\Entity\Post;
use App\Entity\Category;
use App\Helper\PostStatistics;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class BlogController extends AbstractController
{
    private const string SLUG_REGEX = '[a-z0-9A-Z\-]+';

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly PostRepository $postRepository
    ) {}

    #[Route('/', name: 'homepage')]
    public function homepage(): Response
    {
        $posts = $this->postRepository->findAllPublished();

        return $this->renderPostsListing($posts);
    }

    #[Route('/category/{slug:category}', name: 'category', requirements: ['slug' => self::SLUG_REGEX])]
    public function category(Category $category): Response
    {
        $posts = $this->postRepository->findAllPublished($category);

        return $this->renderPostsListing($posts, ['category' => $category]);
    }

    #[Route('/tag/{slug:tag}', name: 'tag', requirements: ['slug' => self::SLUG_REGEX])]
    public function tag(Tag $tag): Response
    {
        $posts = $this->postRepository->findAllPublished(tag: $tag);

        return $this->renderPostsListing($posts, ['tag' => $tag]);
    }

    #[Route('/post/{slug:post}', name: 'show', methods: ['GET'], requirements: ['slug' => self::SLUG_REGEX])]
    public function show(Post $post): Response
    {
        return $this->render('blog/show.html.twig', [
            'categories' => $this->categoryRepository->findAllOrdered(),
            'post' => $post,
        ]);
    }

    #[Route('/post/{slug:post}/view-increment', name: 'view_increment', methods: ['POST'], requirements: ['slug' => self::SLUG_REGEX])]
    public function incrementView(Post $post, PostStatistics $postStatistics): JsonResponse
    {
        try {
            $postStatistics->incrementNumberOfViews($post);

            return new JsonResponse(['success' => true]);
        } catch (\Throwable $th) {
            return new JsonResponse(['error' => $th->getMessage()]);
        }
    }

    private function renderPostsListing(array $posts, array $params = []): Response
    {
        return $this->render(
            'blog/index.html.twig',
            array_merge([
                'categories' => $this->categoryRepository->findAllOrdered(),
                'posts' => $posts,
            ], $params)
        );
    }
}
