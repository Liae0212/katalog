<?php

namespace App\Tests\Controller;

use App\Entity\Comment;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Testy kontrolera komentarzy.
 */
class CommentControllerTest extends WebTestCase
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
     * Inicjalizacja klienta i menedżera encji.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Loguje użytkownika o e-mailu admin@example.com.
     * Tworzy użytkownika, jeśli nie istnieje.
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
            $user->setRoles(['ROLE_USER']);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->client->loginUser($user);
    }

    /**
     * Tworzy i zwraca kategorię testową.
     *
     * @return Category
     */
    private function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('Test Category');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    /**
     * Testuje proces tworzenia komentarza.
     */
    public function testCreateComment(): void
    {
        $this->logIn();
        $category = $this->createCategory();

        $task = new Task();
        $task->setTitle('Zadanie do komentarza');
        $task->setCategory($category);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/comment/create/' . $task->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Zapisz')->form();
        $form['comment[content]'] = 'To jest testowy komentarz';
        $form['comment[nick]'] = 'Tester';

        $this->client->submit($form);

        $this->assertResponseRedirects('/task');
    }

    /**
     * Testuje proces edycji istniejącego komentarza.
     */
    public function testEditComment(): void
    {
        $this->logIn();
        $category = $this->createCategory();

        $task = new Task();
        $task->setTitle('Zadanie testowe');
        $task->setCategory($category);
        $this->entityManager->persist($task);

        $comment = new Comment();
        $comment->setContent('Stary komentarz');
        $comment->setNick('Tester');
        $comment->setTask($task);
        $comment->setAuthor($this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']));

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/comment/' . $comment->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'comment[content]' => 'Zaktualizowany komentarz',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/task');
    }

    /**
     * Testuje proces usuwania komentarza.
     */
    public function testDeleteComment(): void
    {
        $this->logIn();
        $category = $this->createCategory();

        $task = new Task();
        $task->setTitle('Zadanie testowe');
        $task->setCategory($category);
        $this->entityManager->persist($task);

        $comment = new Comment();
        $comment->setContent('Komentarz do usunięcia');
        $comment->setNick('Tester');
        $comment->setTask($task);
        $comment->setAuthor($this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']));

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/comment/' . $comment->getId() . '/delete');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Usuń')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/task');
    }
}
