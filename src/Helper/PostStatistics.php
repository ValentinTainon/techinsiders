<?php

namespace App\Helper;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;

class PostStatistics
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function incrementNumberOfViews(Post $post): void
    {
        $post->setNumberOfViews($post->getNumberOfViews() + 1);

        $this->entityManager->flush();
    }
}
