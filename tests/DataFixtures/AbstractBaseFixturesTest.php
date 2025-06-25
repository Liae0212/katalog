<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\AbstractBaseFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Test klasy AbstractBaseFixtures.
 */
class AbstractBaseFixturesTest extends TestCase
{
    /**
     * Mock ObjectManager do testów.
     *
     * @var ObjectManager&MockObject
     */
    private ObjectManager&MockObject $objectManager;

    /**
     * Przygotowanie testu - tworzy mock ObjectManager.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
    }

    /**
     * Test metody createMany w AbstractBaseFixtures.
     * Sprawdza, czy metoda tworzy i zapisuje poprawną liczbę obiektów
     * oraz czy indeksy tych obiektów są poprawnie ustawione.
     *
     * @return void
     */
    public function testCreateMany(): void
    {
        $fixtures = new class extends AbstractBaseFixtures {
            /**
             * Tablica przechowująca obiekty zapisane przez persist.
             *
             * @var array
             */
            public array $persisted = [];

            /**
             * Implementacja abstrakcyjnej metody ładującej dane.
             *
             * @return void
             */
            protected function loadData(): void
            {
                $this->createMany(2, 'test', function (int $i) {
                    $obj = new \stdClass();
                    $obj->index = $i;
                    return $obj;
                });
            }

            /**
             * Zwraca zapisane obiekty.
             *
             * @return array
             */
            public function getPersisted(): array
            {
                return $this->persisted;
            }

            /**
             * Symulacja zapisu obiektu.
             *
             * @param object $entity
             * @return void
             */
            public function persist($entity): void
            {
                $this->persisted[] = $entity;
            }

            /**
             * Symulacja flush (brak działania w teście).
             *
             * @return void
             */
            public function flush(): void
            {
            }

            /**
             * Symulacja dodania referencji (brak działania w teście).
             *
             * @param string $name
             * @param object $entity
             * @return void
             */
            public function addReference(string $name, object $entity): void
            {
                // no-op for testing
            }
        };

        $fixturesReflection = new \ReflectionClass($fixtures);
        $managerProperty = $fixturesReflection->getParentClass()->getProperty('manager');
        $managerProperty->setAccessible(true);
        $managerProperty->setValue($fixtures, new class($fixtures) implements ObjectManager {
            private $parent;

            /**
             * Konstruktor klasy anonimowej ObjectManager mock.
             *
             * @param object $parent Referencja do klasy nadrzędnej
             */
            public function __construct($parent)
            {
                $this->parent = $parent;
            }

            /**
             * Przekierowanie persist do klasy nadrzędnej.
             *
             * @param object $object
             * @return void
             */
            public function persist($object): void
            {
                $this->parent->persist($object);
            }

            /**
             * Przekierowanie flush do klasy nadrzędnej.
             *
             * @return void
             */
            public function flush(): void
            {
                $this->parent->flush();
            }

            public function find($className, $id) {}
            public function remove($object) {}
            public function merge($object) {}
            public function clear($objectName = null) {}
            public function detach($object) {}
            public function refresh($object) {}
            public function getRepository($className) {}
            public function getClassMetadata($className) {}
            public function getMetadataFactory() {}
            public function initializeObject($obj) {}
            public function contains($object) {}
        });

        $fixtures->load($fixturesReflection->getParentClass()->getProperty('manager')->getValue($fixtures));

        $persisted = $fixtures->getPersisted();
        $this->assertCount(2, $persisted);
        $this->assertEquals(0, $persisted[0]->index);
        $this->assertEquals(1, $persisted[1]->index);
    }
}
