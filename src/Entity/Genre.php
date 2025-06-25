<?php

/**
 * Genre entity.
 */

namespace App\Entity;

use App\Repository\GenreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Genre.
 */
#[ORM\Entity(repositoryClass: GenreRepository::class)]
class Genre
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
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
     * Genre.
     */
    #[ORM\Column(length: 255)]
    private ?string $genre = null;

    /**
     * Tasks.
     *
     * @var Collection|ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'genre', targetEntity: Task::class)]
    private Collection $tasks;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    /**
     * Getter for the ID.
     *
     * @return int|null The ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for the creation date.
     *
     * @return \DateTimeImmutable|null The creation date
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for the creation date.
     *
     * @param \DateTimeImmutable|null $createdAt The creation date
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Getter for the update date.
     *
     * @return \DateTimeImmutable|null The update date
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter for the update date.
     *
     * @param \DateTimeImmutable|null $updatedAt The update date
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Getter for the genre.
     *
     * @return string|null The genre
     */
    public function getGenre(): ?string
    {
        return $this->genre;
    }

    /**
     * Setter for the genre.
     *
     * @param string $genre The genre
     */
    public function setGenre(string $genre): void
    {
        $this->genre = $genre;
    }

    /**
     * Getter for the tasks.
     *
     * @return Collection<int, Task> The tasks collection
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    /**
     * Add a task to the genre.
     *
     * @param Task $task The task to add
     */
    public function addTask(Task $task): void
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setGenre($this);
        }
    }

    /**
     * Remove a task from the genre.
     *
     * @param Task $task The task to remove
     */
    public function removeTask(Task $task): void
    {
        if ($this->tasks->removeElement($task) && $task->getGenre() === $this) {
            $task->setGenre(null);
        }
    }
}
