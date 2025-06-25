<?php
/**
 * Artist entity.
 */

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

/**
 * Class Artist.
 */
#[ORM\Entity(repositoryClass: ArtistRepository::class)]
class Artist
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
    #[ORM\Column]
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
     * Name.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * Tasks.
     */
    #[ORM\OneToMany(mappedBy: 'artist', targetEntity: Task::class)]
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
     * Getter for the name.
     *
     * @return string|null The name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Setter for the name.
     *
     * @param string $name The name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
     * Add a task to the artist.
     *
     * @param Task $task The task to add
     */
    public function addTask(Task $task): void
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setArtist($this);
        }
    }

    /**
     * Remove a task from the artist.
     *
     * @param Task $task The task to remove
     */
    public function removeTask(Task $task): void
    {
        if ($this->tasks->removeElement($task) && $task->getArtist() === $this) {
            $task->setArtist(null);
        }
    }
}
