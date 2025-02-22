<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Tag;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\UserRole;
use App\Entity\Comment;
use App\Entity\Category;
use App\Enum\PostStatus;
use App\Repository\UserRepository;
use Ottaviano\Faker\Gravatar;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Smknstd\FakerPicsumImages\FakerPicsumImagesProvider;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new FakerPicsumImagesProvider($faker));
        $faker->addProvider(new Gravatar($faker));

        // Generate 1 Super Admin
        $superAdmin = new User();
        $superAdmin->setUsername('val')
            ->setEmail('val@mail.fr')
            ->setPassword(
                $this->passwordHasher->hashPassword(
                    $superAdmin,
                    "admin"
                )
            )
            ->setRole(UserRole::SUPER_ADMIN)
            ->setAvatar($faker->gravatarUrl())
            ->setAbout($faker->text())
            ->setVerified(true);

        $manager->persist($superAdmin);

        // Generate 15 users
        $usersName = [];
        $uniqueUsersName = [];
        for ($i = 0; $i < 15; $i++) {
            $usersName[] = $faker->unique()->userName();
        }

        foreach ($usersName as $index => $userName) {
            $userName = "$userName-$index";
            $uniqueUsersName[] = $userName;

            $user = new User();
            $user->setUsername($userName)
                ->setEmail($faker->email())
                ->setPassword(
                    $this->passwordHasher->hashPassword(
                        $user,
                        "password"
                    )
                )
                ->setRole($faker->randomElement(
                    [UserRole::GUEST, UserRole::USER, UserRole::EDITOR, UserRole::ADMIN]
                ))
                ->setAvatar($faker->gravatarUrl())
                ->setAbout($faker->text())
                ->setVerified(true);

            $manager->persist($user);
            $this->addReference($userName, $user);
        }

        // Generate 10 Categories
        $categoryNames = $faker->unique()->words(10);
        $uniqueCategoryNames = [];
        foreach ($categoryNames as $index => $categoryName) {
            $categoryName = "$categoryName-$index";
            $uniqueCategoryNames[] = $categoryName;

            $category = new Category();
            $category->setName($categoryName)
                ->setSlug($this->slugger->slug($categoryName));

            $manager->persist($category);
            $this->addReference($categoryName, $category);
        }

        // Generate 25 Tags
        $tagNames = $faker->unique()->words(25);
        $uniqueTagNames = [];
        foreach ($tagNames as $index => $tagName) {
            $tagName = "$tagName-$index";
            $uniqueTagNames[] = $tagName;

            $tag = new Tag();
            $tag->setName($tagName)
                ->setSlug($this->slugger->slug($tagName));

            $manager->persist($tag);
            $this->addReference($tagName, $tag);
        }

        // Generate 20 Posts
        $postsTitle = $faker->unique()->sentences(20);
        foreach ($postsTitle as $postTitle) {
            $post = new Post();
            $post->setTitle($postTitle)
                ->setSlug($this->slugger->slug($postTitle))
                ->setThumbnail($faker->imageUrl(width: 800, height: 600))
                ->setContent($faker->paragraphs(5, true))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setUpdatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setStatus(PostStatus::PUBLISHED)
                ->setUser($this->getReference($faker->randomElement($uniqueUsersName), User::class))
                ->setCategory($this->getReference($faker->randomElement($uniqueCategoryNames), Category::class))
                ->addTag($this->getReference($faker->randomElement($uniqueTagNames), Tag::class));

            $manager->persist($post);
            $this->addReference($postTitle, $post);
        }

        // Generate Comments
        foreach ($uniqueUsersName as $user) {
            $comment = new Comment();
            $comment->setContent($faker->paragraph())
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setUpdatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setUser($this->getReference($user, User::class))
                ->setPost($this->getReference($faker->randomElement($postsTitle), Post::class));

            $manager->persist($comment);
        }

        $manager->flush();
    }
}
