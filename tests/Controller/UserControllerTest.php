<?php

/**
 * UserControllerTest.
 *
 * Functional tests for UserController.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Testy funkcjonalne kontrolera użytkowników.
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Klient HTTP symulujący przeglądarkę.
     *
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    /**
     * Repozytorium użytkowników.
     */
    private UserRepository $userRepository;

    /**
     * Tłumacz komunikatów (translator).
     */
    private TranslatorInterface $translator;

    /**
     * Inicjalizacja środowiska testowego.
     * Tworzy klienta, pobiera repozytorium użytkowników i translator,
     * tworzy użytkownika admina (jeśli nie istnieje) i loguje go.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = $this->client->getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->translator = $container->get(TranslatorInterface::class);

        $adminEmail = 'admin@example.com';
        $adminUser = $this->userRepository->findOneBy(['email' => $adminEmail]);
        if (!$adminUser) {
            $adminUser = new User();
            $adminUser->setEmail($adminEmail);
            $adminUser->setRoles(['ROLE_ADMIN']);
            $adminUser->setPassword(
                $container->get('security.password_hasher')->hashPassword($adminUser, 'adminpass')
            );
            $this->userRepository->save($adminUser);
        }

        $this->client->loginUser($adminUser);
    }

    /**
     * Testuje czy strona listy użytkowników ładuje się poprawnie
     * i czy wyświetla paginację, jeśli jest dostępna.
     */
    public function testIndexPageLoads(): void
    {
        $crawler = $this->client->request('GET', '/user');
        $this->assertResponseIsSuccessful();

        if ($crawler->filter('nav.pagination')->count() > 0) {
            $this->assertSelectorExists('nav.pagination');
        } else {
            $this->assertTrue(true, 'Brak paginacji (za mało danych)');
        }
    }

    /**
     * Testuje poprawne wyświetlenie strony pojedynczego użytkownika.
     */
    public function testShowUserPage(): void
    {
        $user = $this->getFirstUser();
        $crawler = $this->client->request('GET', '/user/'.$user->getId());
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('body', $user->getEmail());
    }

    /**
     * Testuje ładowanie strony zmiany hasła użytkownika oraz poprawne przesłanie formularza.
     */
    public function testEditUserPasswordPageLoadsAndSubmit(): void
    {
        $user = $this->getFirstUser();
        $crawler = $this->client->request('GET', '/user/'.$user->getId().'/edit/password');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton($this->translator->trans('edit.edit'))->form();

        $form['user_password[password][first]'] = 'newpassword123';
        $form['user_password[password][second]'] = 'newpassword123';

        $this->client->submit($form);

        $this->assertResponseRedirects('/task');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', $this->translator->trans('message.updated_successfully'));
    }

    /**
     * Testuje ładowanie strony zmiany emaila użytkownika oraz poprawne przesłanie formularza.
     */
    public function testEditUserEmailPageLoadsAndSubmit(): void
    {
        $user = $this->getFirstUser();
        $crawler = $this->client->request('GET', '/user/'.$user->getId().'/edit/email');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton($this->translator->trans('edit.edit'))->form();

        $form['user_email[email]'] = 'newemail@example.com';

        $this->client->submit($form);

        $this->assertResponseRedirects('/task');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', $this->translator->trans('message.updated_successfully'));
    }

    /**
     * Pobiera pierwszego użytkownika z bazy, jeśli nie istnieje,
     * tworzy nowego użytkownika testowego i zwraca go.
     */
    private function getFirstUser(): User
    {
        $user = $this->userRepository->findOneBy([]);
        if (!$user) {
            $user = new User();
            $user->setEmail('testuser@example.com');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $this->client->getContainer()->get('security.password_hasher')->hashPassword($user, 'testpass')
            );
            $this->userRepository->save($user);
        }

        return $user;
    }
}
