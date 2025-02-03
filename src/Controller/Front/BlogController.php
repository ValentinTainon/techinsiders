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

#[Route(path: '/{_locale}', requirements: ['_locale' => 'fr|en'])]
final class BlogController extends AbstractController
{
    private const string SLUG_REGEX = '[a-z0-9A-Z\-]+';

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly PostRepository $postRepository
    ) {}

    #[Route(path: '', name: 'homepage')]
    public function homepage(): Response
    {
        $posts = $this->postRepository->findAllPublished();

        return $this->renderPostsListing($posts);
    }

    #[Route(path: [
        'fr' => '/categorie/{slug:category}',
        'en' => '/category/{slug:category}',
    ], name: 'category', requirements: ['slug' => self::SLUG_REGEX])]
    public function category(Category $category): Response
    {
        $posts = $this->postRepository->findAllPublished($category);

        return $this->renderPostsListing($posts, ['category' => $category]);
    }

    #[Route(path: [
        'fr' => '/mot-cle/{slug:tag}',
        'en' => '/tag/{slug:tag}',
    ], name: 'tag', requirements: ['slug' => self::SLUG_REGEX])]
    public function tag(Tag $tag): Response
    {
        $posts = $this->postRepository->findAllPublished(tag: $tag);

        return $this->renderPostsListing($posts, ['tag' => $tag]);
    }

    #[Route(path: [
        'fr' => '/article/{slug:post}',
        'en' => '/post/{slug:post}',
    ], name: 'show', methods: ['GET'], requirements: ['slug' => self::SLUG_REGEX])]
    public function show(Post $post): Response
    {
        return $this->render('blog/show.html.twig', [
            'categories' => $this->categoryRepository->findAllOrdered(),
            'post' => $post,
        ]);
    }

    #[Route(path: [
        'fr' => '/article/{slug:post}/incrementer-nombre-de-vues',
        'en' => '/post/{slug:post}/increment-number-of-views',
    ], name: 'increment_number_of_views', methods: ['POST'], requirements: ['slug' => self::SLUG_REGEX])]
    public function incrementViews(Post $post, PostStatistics $postStatistics): JsonResponse
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
