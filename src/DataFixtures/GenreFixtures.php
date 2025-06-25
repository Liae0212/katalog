<?php

/**
 * Genre fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Genre;

/**
 * Class GenreFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class GenreFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        $this->createMany(30, 'genres', function (int $i) {
            $genre = new Genre();
            $genre->setGenre($this->faker->unique()->word);
            $genre->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $genre->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            return $genre;
        });

        $this->manager->flush();
    }
}
