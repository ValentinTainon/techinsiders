<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'tag.unique.entity.constraint.name.message')]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\NoSuspiciousCharacters]
    private ?string $name = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\NoSuspiciousCharacters]
    private ?string $slug = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'tags', cascade: ['persist'])]
    private Collection $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getPostsCount(): int
    {
        return $this->posts->count();
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        $this->posts->removeElement($post);

        return $this;
    }
}
