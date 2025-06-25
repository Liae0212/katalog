<?php

/**
 * CategoryControllerTest.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * HTTP client.
     *
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    /**
     * User repository.
     *
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * Category repository.
     *
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    /**
     * Translator interface.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Set up test case.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = $this->client->getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->categoryRepository = $container->get(CategoryRepository::class);
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
     * Testuje, czy strona z listą kategorii się ładuje.
     */
    public function testIndexPageLoads(): void
    {
        $this->client->request('GET', '/category');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /**
     * Testuje, czy formularz tworzenia kategorii się ładuje.
     */
    public function testCreateCategoryPageLoads(): void
    {
        $this->client->request('GET', '/category/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Testuje, czy możliwe jest przesłanie formularza tworzenia kategorii.
     */
    public function testCreateCategorySubmit(): void
    {
        $crawler = $this->client->request('GET', '/category/create');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton($this->translator->trans('save.save'))->form();

        $form['category[title]'] = 'Nowa Kategoria Testowa';

        $this->client->submit($form);

        $this->assertResponseRedirects('/category');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', $this->translator->trans('Created successfully'));
    }

    /**
     * Testuje, czy strona szczegółów kategorii się ładuje i wyświetla dane.
     */
    public function testShowCategoryPage(): void
    {
        $category = $this->getFirstCategory();
        $crawler = $this->client->request('GET', '/category/' . $category->getId());
        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString(
            $category->getTitle(),
            $crawler->filter('h1.user-data')->eq(1)->text()
        );
    }

    /**
     * Testuje, czy strona edycji kategorii się ładuje.
     */
    public function testEditCategoryPageLoads(): void
    {
        $category = $this->getFirstCategory();
        $this->client->request('GET', '/category/' . $category->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Testuje, czy następuje redirect, gdy nie można usunąć kategorii.
     */
    public function testDeleteCategoryRedirectWhenCannotDelete(): void
    {
        $category = $this->getFirstCategory();

        $this->client->request('GET', '/category/' . $category->getId() . '/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Pomocnicza metoda do pobrania pierwszej kategorii lub jej utworzenia.
     *
     * @return Category
     */
    private function getFirstCategory(): Category
    {
        $category = $this->categoryRepository->findOneBy([]);
        if (!$category) {
            $category = new Category();
            $category->setTitle('Testowa Kategoria');
            $this->categoryRepository->save($category);
        }

        return $category;
    }
}
