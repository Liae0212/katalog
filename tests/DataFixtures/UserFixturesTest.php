<?php

/**
 * UserFixturesTest.
 *
 * Unit test for the UserFixtures class.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Entity\Enum\UserRole;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Test klasy UserFixtures.
 */
class UserFixturesTest extends TestCase
{
    /**
     * Testuje metodę loadData, która powinna utworzyć
     * 10 użytkowników i 3 administratorów z odpowiednimi
     * rolami, emailami oraz zahashowanymi hasłami.
     */
    public function testLoadDataCreatesUsersAndAdmins(): void
    {

        $managerMock = $this->createMock(ObjectManager::class);

        $persisted = [];
        $managerMock->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            if ($entity instanceof User) {
                $persisted[] = $entity;
            }
        });

        $managerMock->expects($this->atLeastOnce())->method('flush');

        $passwordHasherMock = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasherMock->method('hashPassword')->willReturn('hashed_password');

        $referenceRepoMock = $this->createMock(ReferenceRepository::class);

        $fixture = new UserFixtures($passwordHasherMock);

        $reflection = new \ReflectionClass($fixture);

        $managerProp = $reflection->getProperty('manager');
        $managerProp->setAccessible(true);
        $managerProp->setValue($fixture, $managerMock);

        $fakerProp = $reflection->getProperty('faker');
        $fakerProp->setAccessible(true);
        $fakerProp->setValue($fixture, Factory::create());

        $refRepoProp = $reflection->getProperty('referenceRepository');
        $refRepoProp->setAccessible(true);
        $refRepoProp->setValue($fixture, $referenceRepoMock);

        $method = $reflection->getMethod('loadData');
        $method->setAccessible(true);
        $method->invoke($fixture);

        $this->assertCount(13, $persisted);

        $users = array_filter($persisted, fn (User $u) => str_starts_with($u->getEmail(), 'user'));
        $admins = array_filter($persisted, fn (User $u) => str_starts_with($u->getEmail(), 'admin'));

        $users = array_values($users);
        $admins = array_values($admins);

        $this->assertCount(10, $users);
        $this->assertCount(3, $admins);

        foreach ($users as $i => $user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals("user{$i}@example.com", $user->getEmail());
            $this->assertEquals([UserRole::ROLE_USER->value], $user->getRoles());
            $this->assertEquals('hashed_password', $user->getPassword());
        }

        foreach ($admins as $i => $admin) {
            $this->assertInstanceOf(User::class, $admin);
            $this->assertEquals("admin{$i}@example.com", $admin->getEmail());
            $this->assertEquals(
                [UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value],
                $admin->getRoles()
            );
            $this->assertEquals('hashed_password', $admin->getPassword());
        }
    }
}
