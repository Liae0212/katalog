<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\CategoryFixtures;
use App\Entity\Category;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Test klasy CategoryFixtures.
 */
class CategoryFixturesTest extends TestCase
{
    /**
     * Testuje, czy metoda loadData tworzy dokładnie 20 obiektów Category,
     * które są poprawnie zapisywane i mają unikalne tytuły.
     *
     * @return void
     */
    public function testLoadDataCreates20Categories(): void
    {
        $managerMock = $this->createMock(ObjectManager::class);

        $persistedEntities = [];

        $managerMock->method('persist')->willReturnCallback(function ($entity) use (&$persistedEntities) {
            if ($entity instanceof Category) {
                $persistedEntities[] = $entity;
            }
        });

        $managerMock->expects($this->atLeastOnce())->method('flush');

        $referenceRepo = $this->createMock(ReferenceRepository::class);

        $fixture = new CategoryFixtures();
        $fixture->setReferenceRepository($referenceRepo);

        $reflection = new \ReflectionClass($fixture);

        $managerProp = $reflection->getProperty('manager');
        $managerProp->setAccessible(true);
        $managerProp->setValue($fixture, $managerMock);

        $fakerProp = $reflection->getProperty('faker');
        $fakerProp->setAccessible(true);
        $fakerProp->setValue($fixture, Factory::create());

        $fixture->loadData();

        $this->assertCount(20, $persistedEntities);
        $this->assertContainsOnlyInstancesOf(Category::class, $persistedEntities);

        $titles = array_map(fn($category) => $category->getTitle(), $persistedEntities);
        $this->assertCount(20, array_unique($titles));
    }
}
