<?php

namespace App\Tests\Controller;

use App\Entity\Tag;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Testy funkcjonalne kontrolera tagów.
 */
class TagControllerTest extends WebTestCase
{
    /**
     * Symfony HTTP client.
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
     * Ustawia środowisko testowe, w tym klienta, menedżera encji oraz testowego użytkownika z rolą admina.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

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

    /**
     * Testuje wyświetlenie strony index z listą tagów.
     *
     * @return void
     */
    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', '/tag');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /**
     * Testuje tworzenie nowego tagu.
     *
     * @return void
     */
    public function testCreateTag(): void
    {
        $crawler = $this->client->request('GET', '/tag/create');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Zapisz')->form();
        $form['tag[title]'] = 'Nowy Tag Testowy';

        $this->client->submit($form);
        $this->assertResponseRedirects('/tag');

        $this->client->followRedirect();

        $tag = $this->entityManager->getRepository(Tag::class)->findOneBy(['title' => 'Nowy Tag Testowy']);
        $this->assertNotNull($tag);
    }

    /**
     * Testuje wyświetlanie pojedynczego tagu.
     *
     * @return void
     */
    public function testShowTag(): void
    {
        $tag = new Tag();
        $tag->setTitle('Tag do testu show');
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $this->client->request('GET', '/tag/' . $tag->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tag');
    }

    /**
     * Testuje proces edycji tagu.
     *
     * @return void
     */
    public function testEditTag(): void
    {
        $tag = new Tag();
        $tag->setTitle('Tag do edycji');
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/tag/' . $tag->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'tag[title]' => 'Zaktualizowany Tag',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/tag');

        $this->client->followRedirect();

        $updatedTag = $this->entityManager->getRepository(Tag::class)->find($tag->getId());
        $this->assertSame('Zaktualizowany Tag', $updatedTag->getTitle());
    }

    /**
     * Testuje usuwanie tagu.
     *
     * @return void
     */
    public function testDeleteTag(): void
    {
        // Utwórz tag do usunięcia
        $tag = new Tag();
        $tag->setTitle('Tag do usunięcia');
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/tag/' . $tag->getId() . '/delete');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Usuń')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/tag');

        $this->client->followRedirect();

        $deletedTag = $this->entityManager->getRepository(Tag::class)->find($tag->getId());
        $this->assertNull($deletedTag);
    }
}
