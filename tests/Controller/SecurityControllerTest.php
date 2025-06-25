<?php

/**
 * SecurityControllerTest.
 *
 * Functional tests for SecurityController.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Testy funkcjonalne dla kontrolera bezpieczeństwa (logowanie, wylogowanie).
 */
class SecurityControllerTest extends WebTestCase
{
    private $client;
    private UserRepository $userRepository;
    private TranslatorInterface $translator;

    /**
     * Ustawia środowisko testowe i tworzy testowego użytkownika.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = $this->client->getContainer()->get(UserRepository::class);
        $this->translator = $this->client->getContainer()->get(TranslatorInterface::class);

        $testUser = new User();
        $testUser->setEmail('testuser@example.com');
        $testUser->setRoles(['ROLE_USER']);
        $testUser->setPassword(
            $this->client->getContainer()->get('security.password_hasher')->hashPassword($testUser, 'testpassword')
        );
        $this->userRepository->save($testUser);
    }

    /**
     * Sprawdza, czy strona logowania jest dostępna.
     */
    public function testLoginPageIsAccessible(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.sign', $this->translator->trans('Please.sign.in'));
    }

    /**
     * Sprawdza, czy strona logowania jest dostępna również dla zalogowanego użytkownika.
     */
    public function testLoginPageAccessibleWhenLoggedIn(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'testuser@example.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.sign', $this->translator->trans('Please.sign.in'));
    }

    /**
     * Sprawdza, czy logowanie kończy się niepowodzeniem przy błędnych danych.
     */
    public function testLoginFailsWithInvalidCredentials(): void
    {
        $this->client->request('GET', '/login');
        $submitLabel = $this->translator->trans('sign.in');
        $this->client->submitForm($submitLabel, [
            'email' => 'testuser@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertResponseRedirects('/login');

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    /**
     * Sprawdza, czy logowanie przebiega pomyślnie przy poprawnych danych.
     */
    public function testLoginSucceedsWithValidCredentials(): void
    {
        $this->client->request('GET', '/login');
        $submitLabel = $this->translator->trans('sign.in');
        $this->client->submitForm($submitLabel, [
            'email' => 'testuser@example.com',
            'password' => 'testpassword',
        ]);

        $this->assertResponseRedirects();
    }

    /**
     * Sprawdza, czy wylogowanie przekierowuje na stronę logowania.
     */
    public function testLogout(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'testuser@example.com']);
        $this->client->loginUser($user);
        $this->client->request('GET', '/logout');

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1.sign', $this->translator->trans('Please.sign.in'));
    }

    /**
     * Testuje prywatną metodę getLoginUrl() z klasy LoginFormAuthenticator.
     */
    public function testGetLoginUrl(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $authenticator = new LoginFormAuthenticator($urlGenerator);

        $reflectionMethod = new \ReflectionMethod(LoginFormAuthenticator::class, 'getLoginUrl');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($authenticator, $this->createMock(\Symfony\Component\HttpFoundation\Request::class));

        $this->assertSame('', $result);
    }

    /**
     * Sprawdza, czy metoda logout() rzuca LogicException zgodnie z oczekiwaniem.
     */
    public function testLogoutThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This method can be blank - it will be intercepted by the logout key on your firewall.');

        $controller = new \App\Controller\SecurityController();
        $controller->logout();
    }

    /**
     * Sprawdza, czy wylogowanie przekierowuje poprawnie do strony logowania.
     */
    public function testLogoutRedirectsToLoginPage(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'testuser@example.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/logout');

        $this->assertResponseRedirects('/login');
    }
}
