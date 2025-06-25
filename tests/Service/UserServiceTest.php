<?php

/**
 * UserServiceTest.
 *
 * Unit tests for the UserService class.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserService;
use App\Service\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class UserServiceTest.
 */
class UserServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * User service.
     */
    private ?UserServiceInterface $userService;

    /**
     * Password hasher service.
     */
    private ?UserPasswordHasherInterface $passwordHasher;

    /**
     * Set up the test environment.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->userService = $container->get(UserService::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
    }

    /**
     * Test saving a new user.
     */
    public function testSaveUser(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('plainPassword');

        $this->userService->save($user);
        $userId = $user->getId();

        $persistedUser = $this->entityManager->getRepository(User::class)->find($userId);
        $this->assertNotNull($persistedUser);
        $this->assertSame('test@example.com', $persistedUser->getEmail());
        $this->assertTrue($this->passwordHasher->isPasswordValid($persistedUser, 'plainPassword'));
        $this->assertContains('ROLE_USER', $persistedUser->getRoles());
    }

    /**
     * Test finding a user by email.
     */
    public function testFindOneBy(): void
    {
        $email = 'findme@example.com';
        $user = new User();
        $user->setEmail($email);
        $user->setPassword('password123');
        $this->userService->save($user);

        $foundUser = $this->userService->findOneBy($email);

        $this->assertNotNull($foundUser);
        $this->assertEquals($email, $foundUser->getEmail());
    }

    /**
     * Test paginated list returns correct number of items.
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $count = 3;

        for ($i = 0; $i < $count; ++$i) {
            $user = new User();
            $user->setEmail("user{$i}@example.com");
            $user->setPassword('password');
            $this->userService->save($user);
        }

        $pagination = $this->userService->getPaginatedList($page);

        $this->assertGreaterThanOrEqual($count, $pagination->count());
    }
}
