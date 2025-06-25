<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Category;
use App\Entity\Task;
use App\Entity\User;

/**
 * Testy funkcjonalne kontrolera zadań (TaskController).
 */
class TaskControllerTest extends WebTestCase
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
     * Konfiguracja środowiska testowego – tworzenie klienta i menedżera encji.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Loguje użytkownika z uprawnieniami administratora.
     *
     * @return void
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

    /**
     * Testuje poprawne przesłanie formularza tworzenia zadania.
     *
     * @return void
     */
    public function testCreateTaskSubmit(): void
    {
        $this->logIn();

        $category = new Category();
        $category->setTitle('Test Category');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/task/create');
        $form = $crawler->selectButton('Zapisz')->form();

        $form['task[title]'] = 'Nowe Zadanie';
        $form['task[category]'] = $category->getId();
        $form['task[tags]'] = 'tag1,tag2';

        $this->client->submit($form);

        $this->assertResponseRedirects('/task');
    }

    /**
     * Testuje poprawne wyświetlenie strony zadania.
     *
     * @return void
     */
    public function testShowTaskPage(): void
    {
        $this->logIn();

        $category = new Category();
        $category->setTitle('Test Category');
        $this->entityManager->persist($category);

        $task = new Task();
        $task->setTitle('Testowe Zadanie');
        $task->setCategory($category);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/task/' . $task->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1, h2, p', 'Piosenka');
    }

    /**
     * Testuje ładowanie strony edycji zadania.
     *
     * @return void
     */
    public function testEditTaskPageLoads(): void
    {
        $this->logIn();

        $category = new Category();
        $category->setTitle('Test Category');
        $this->entityManager->persist($category);

        $task = new Task();
        $task->setTitle('Zadanie do edycji');
        $task->setCategory($category);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->client->request('GET', '/task/' . $task->getId() . '/edit');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Testuje usunięcie zadania.
     *
     * @return void
     */
    public function testDeleteTask(): void
    {
        $this->logIn();

        $category = new Category();
        $category->setTitle('Test Category');
        $this->entityManager->persist($category);

        $task = new Task();
        $task->setTitle('Zadanie do usunięcia');
        $task->setCategory($category);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/task/' . $task->getId() . '/delete');

        $form = $crawler->selectButton('Usuń')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/task');
    }
}
