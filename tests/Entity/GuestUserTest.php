<?php

/**
 * GuestUserTest.
 *
 * Unit test for the GuestUser class.
 */

namespace App\Tests\Entity;

use App\Entity\GuestUser;
use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe klasy GuestUser.
 */
class GuestUserTest extends TestCase
{
    /**
     * Testuje, że początkowo id jest null.
     */
    public function testGetIdInitiallyNull(): void
    {
        $guestUser = new GuestUser();
        $this->assertNull($guestUser->getId());
    }

    /**
     * Testuje getter i setter dla pola email.
     */
    public function testGetSetEmail(): void
    {
        $guestUser = new GuestUser();
        $email = 'test@example.com';
        $guestUser->setEmail($email);
        $this->assertSame($email, $guestUser->getEmail());
    }

    /**
     * Testuje ustawienie nieprawidłowego formatu emaila.
     */
    public function testEmailFormat(): void
    {
        $guestUser = new GuestUser();

        $invalidEmail = 'not-an-email';

        $guestUser->setEmail($invalidEmail);
        $this->assertSame($invalidEmail, $guestUser->getEmail());
    }
}
