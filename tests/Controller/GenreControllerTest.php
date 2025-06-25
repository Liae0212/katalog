<?php

/**
 * GenreControllerTest.
 *
 * Functional tests for GenreController.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Genre;
use App\Entity\User;

/**
 * Testy funkcjonalności kontrolera gatunków (GenreController).
 */
class GenreControllerTest extends WebTestCase
{
    /**
     * Symfony test client.
     *
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    /**
     * Menedżer encji Doctrine.
     *
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * Testuje tworzenie nowego gatunku (Genre).
     */
    public function testCreateGenre(): void
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/genres/create');
        $form = $crawler->selectButton('Zapisz')->form();

        $form['genre[genre]'] = 'Nowy Gatunek';
        $this->client->submit($form);

        $this->assertResponseRedirects('/genres');
    }

    /**
     * Testuje wyświetlanie pojedynczego gatunku.
     */
    public function testShowGenre(): void
    {
        $genre = new Genre();
        $genre->setGenre('Testowy Gatunek');

        $this->entityManager->persist($genre);
        $this->entityManager->flush();

        $this->client->request('GET', '/genres/'.$genre->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1, h2, p', 'Gatunek');
    }

    /**
     * Testuje edycję istniejącego gatunku.
     */
    public function testEditGenre(): void
    {
        $this->logIn();

        $genre = new Genre();
        $genre->setGenre('Do edycji');
        $this->entityManager->persist($genre);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/genres/'.$genre->getId().'/edit');
        $form = $crawler->selectButton('Edytuj')->form();
        $form['genre[genre]'] = 'Zmieniony Gatunek';

        $this->client->submit($form);
        $this->assertResponseRedirects('/genres');
    }

    /**
     * Testuje usunięcie gatunku.
     */
    public function testDeleteGenre(): void
    {
        $this->logIn();

        $genre = new Genre();
        $genre->setGenre('Do usunięcia');
        $this->entityManager->persist($genre);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/genres/'.$genre->getId().'/delete');
        $form = $crawler->selectButton('Usuń')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/genres');
    }

    /**
     * Inicjalizacja klienta i menedżera encji.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Loguje użytkownika z rolą administratora.
     * Tworzy go, jeśli nie istnieje.
     */
    private function logIn(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        if (!$user) {
            $user = new User();
            $user->setEmail('admin@example.com');
            $user->setPassword(password_hash('test', PASSWORD_BCRYPT));
            $user->setRoles(['ROLE_ADMIN']);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->client->loginUser($user);
    }
}
