<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe encji User.
 */
class UserTest extends TestCase
{
    /**
     * Testuje, że początkowo id jest null.
     *
     * @return void
     */
    public function testGetIdInitiallyNull(): void
    {
        $user = new User();
        $this->assertNull($user->getId());
    }

    /**
     * Testuje setter i getter dla adresu e-mail oraz powiązane metody identyfikatora użytkownika.
     *
     * @return void
     */
    public function testSetAndGetEmail(): void
    {
        $user = new User();
        $email = 'user@example.com';

        $user->setEmail($email);
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($email, $user->getUserIdentifier());
        $this->assertSame($email, $user->getUsername());
    }

    /**
     * Testuje setter i getter dla hasła.
     *
     * @return void
     */
    public function testSetAndGetPassword(): void
    {
        $user = new User();
        $password = 'securepassword123';

        $user->setPassword($password);
        $this->assertSame($password, $user->getPassword());
    }

    /**
     * Testuje, że w rolach użytkownika zawsze znajduje się rola USER.
     *
     * @return void
     */
    public function testRolesAlwaysIncludeRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();
        $this->assertContains(UserRole::ROLE_USER->value, $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertCount(2, array_unique($roles));
    }

    /**
     * Testuje, że metoda getSalt zwraca null.
     *
     * @return void
     */
    public function testGetSaltReturnsNull(): void
    {
        $user = new User();
        $this->assertNull($user->getSalt());
    }

    /**
     * Testuje, że metoda eraseCredentials nie rzuca wyjątków.
     *
     * @return void
     */
    public function testEraseCredentialsDoesNotThrow(): void
    {
        $user = new User();
        $this->expectNotToPerformAssertions();
        $user->eraseCredentials();
    }

    /**
     * Testuje dodawanie i usuwanie komentarzy powiązanych z użytkownikiem.
     *
     * @return void
     */
    public function testAddAndRemoveComment(): void
    {
        $user = new User();
        $comment = new Comment();

        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('comments');
        $property->setAccessible(true);

        $this->assertCount(0, $property->getValue($user));

        $user->addComment($comment);
        $this->assertCount(1, $property->getValue($user));
        $this->assertSame($user, $comment->getAuthor());

        $user->removeComment($comment);
        $this->assertCount(0, $property->getValue($user));
        $this->assertNull($comment->getAuthor());
    }
}
