<?php
/**
 * Task fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\Artist;
use App\Entity\Genre;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class TaskFixtures.
 */
class TaskFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof \Doctrine\Persistence\ObjectManager || !$this->faker instanceof \Faker\Generator) {
            return;
        }

        $this->createMany(100, 'tasks', function () {
            $task = new Task();
            $task->setTitle($this->faker->sentence);
            $task->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $task->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            /** @var Category $category */
            $category = $this->getRandomReference('categories', Category::class);
            $task->setCategory($category);

            /** @var Artist $artist */
            $artist = $this->getRandomReference('artists', Artist::class);
            $task->setArtist($artist);

            /** @var Genre $genre */
            $genre = $this->getRandomReference('genres', Genre::class);
            $task->setGenre($genre);

            /** @var User $user */
            $user = $this->getRandomReference('users', User::class);
            $task->setUsers($user);

            /** @var array<Tag> $tags */
            $tags = $this->getRandomReferenceList('tags', Tag::class, $this->faker->numberBetween(0, 5));
            foreach ($tags as $tag) {
                $task->addTag($tag);
            }

            return $task;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class, 1: TagFixtures::class, 2:  ArtistFixtures::class, 3: GenreFixtures::class, 4:UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [CategoryFixtures::class, TagFixtures::class, ArtistFixtures::class, GenreFixtures::class, UserFixtures::class];
    }
}
