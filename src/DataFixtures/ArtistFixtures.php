<?php

/**
 * Artist fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Artist;

/**
 * Class ArtistFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class ArtistFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        $this->createMany(30, 'artists', function (int $i) {
            $artist = new Artist();
            $artist->setName($this->faker->unique()->word);
            $artist->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $artist->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            return $artist;
        });

        $this->manager->flush();
    }
}
