<?php

/**
 * AbstractBaseFixturesTest.
 *
 * Unit test for the AbstractBaseFixtures class.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\AbstractBaseFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the AbstractBaseFixtures class.
 */
class AbstractBaseFixturesTest extends TestCase
{
    /**
     * Mock ObjectManager used in tests.
     */
    private ObjectManager&MockObject $objectManager;

    /**
     * Test the createMany method of AbstractBaseFixtures.
     * Verifies the correct number of objects are created and persisted
     * with appropriate indices.
     */
    public function testCreateMany(): void
    {
        $fixtures = new class () extends AbstractBaseFixtures {
            /**
             * Persisted objects.
             */
            public array $persisted = [];

            /**
             * Load data implementation.
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
             * Get persisted objects.
             */
            public function getPersisted(): array
            {
                return $this->persisted;
            }

            /**
             * Simulate persist.
             *
             * @param object $entity
             */
            public function persist($entity): void
            {
                $this->persisted[] = $entity;
            }

            /**
             * Simulate flush (no-op in test).
             */
            public function flush(): void
            {
                // no-op
            }

            /**
             * Simulate adding reference (no-op in test).
             */
            public function addReference(string $name, object $entity): void
            {
                // no-op
            }
        };

        $fixturesReflection = new \ReflectionClass($fixtures);
        $managerProperty = $fixturesReflection->getParentClass()->getProperty('manager');
        $managerProperty->setAccessible(true);
        $managerProperty->setValue($fixtures, new class ($fixtures) implements ObjectManager {
            private $parent;

            /**
             * Constructor.
             *
             * @param object $parent reference to parent test class
             */
            public function __construct($parent)
            {
                $this->parent = $parent;
            }

            public function persist($object): void
            {
                $this->parent->persist($object);
            }

            public function flush(): void
            {
                $this->parent->flush();
            }

            public function find($className, $id)
            {
            }

            public function remove($object)
            {
            }

            public function merge($object)
            {
            }

            public function clear($objectName = null)
            {
            }

            public function detach($object)
            {
            }

            public function refresh($object)
            {
            }

            public function getRepository($className)
            {
            }

            public function getClassMetadata($className)
            {
            }

            public function getMetadataFactory()
            {
            }

            public function initializeObject($obj)
            {
            }

            public function contains($object)
            {
            }
        });

        $fixtures->load($fixturesReflection->getParentClass()->getProperty('manager')->getValue($fixtures));

        $persisted = $fixtures->getPersisted();
        $this->assertCount(2, $persisted);
        $this->assertEquals(0, $persisted[0]->index);
        $this->assertEquals(1, $persisted[1]->index);
    }

    /**
     * Prepare test case – create ObjectManager mock.
     */
    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
    }
}
