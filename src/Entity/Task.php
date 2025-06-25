<?php

/**
 * Task entity.
 */

namespace App\Entity;

use App\Repository\TaskRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Task.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'tasks')]
class Task
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Created at.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Updated at.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Title.
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    /**
     * Category.
     */
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * Tags.
     *
     * @var Collection<int, Tag>
     */
    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'tasks', fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'tasks_tags')]
    private Collection $tags;

    /**
     * Artist.
     */
    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Artist $artist = null;

    /**
     * Genre.
     */
    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Genre $genre = null;

    /**
     * User.
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Type(User::class)]
    private ?User $users = null;

    /**
     * Comments.
     */
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Comment::class, cascade: ['remove'])]
    private $comments;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for created at.
     *
     * @return \DateTimeImmutable|null Created at
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for created at.
     *
     * @param \DateTimeImmutable|null $createdAt Created at
     */
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Getter for updated at.
     *
     * @return \DateTimeImmutable|null Updated at
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter for updated at.
     *
     * @param \DateTimeImmutable|null $updatedAt Updated at
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Getter for title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string|null $title Title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Getter for category.
     *
     * @return Category|null Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Setter for category.
     *
     * @param Category|null $category Category
     */
    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    /**
     * Getter for tags.
     *
     * @return Collection<int, Tag> Tags collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add tag.
     *
     * @param Tag $tag Tag entity
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }
    }

    /**
     * Remove tag.
     *
     * @param Tag $tag Tag entity
     */
    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Getter for artist.
     *
     * @return Artist|null Artist
     */
    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    /**
     * Setter for artist.
     *
     * @param Artist|null $artist Artist
     */
    public function setArtist(?Artist $artist): void
    {
        $this->artist = $artist;
    }

    /**
     * Getter for genre.
     *
     * @return Genre|null Genre
     */
    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    /**
     * Setter for genre.
     *
     * @param Genre|null $genre Genre
     */
    public function setGenre(?Genre $genre): void
    {
        $this->genre = $genre;
    }

    /**
     * Getter for users.
     *
     * @return User|null User entity
     */
    public function getUsers(): ?User
    {
        return $this->users;
    }

    /**
     * Setter for users.
     *
     * @param User|null $users User entity
     */
    public function setUsers(?User $users): void
    {
        $this->users = $users;
    }

    /**
     * Get the comments associated with this task.
     *
     * @return Collection Comments collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }
}
