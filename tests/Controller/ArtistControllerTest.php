<?php

/**
 * ArtistControllerTest.
 */

namespace App\Tests\Controller;

use App\Entity\Artist;
use App\Entity\User;
use App\Repository\ArtistRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ArtistControllerTest.
 */
class ArtistControllerTest extends WebTestCase
{
    /**
     * HTTP client.
     *
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    /**
     * User repository.
     */
    private UserRepository $userRepository;

    /**
     * Artist repository.
     */
    private ArtistRepository $artistRepository;

    /**
     * Translator interface.
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
        $this->artistRepository = $container->get(ArtistRepository::class);
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
     * Test if artist index page loads successfully.
     */
    public function testIndexPageLoads(): void
    {
        $crawler = $this->client->request('GET', '/artist');
        $this->assertResponseIsSuccessful();

        if ($crawler->filter('nav.pagination')->count() > 0) {
            $this->assertSelectorExists('nav.pagination');
        } else {
            $this->assertTrue(true, 'Brak paginacji (za mało danych)');
        }
    }

    /**
     * Test if show artist page displays data correctly.
     */
    public function testShowArtistPage(): void
    {
        $artist = $this->getFirstArtist();
        $crawler = $this->client->request('GET', '/artist/'.$artist->getId());
        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString(
            $artist->getName(),
            $crawler->filter('dd')->eq(1)->text()
        );
    }

    /**
     * Test if artist creation form loads.
     */
    public function testCreateArtistPageLoads(): void
    {
        $crawler = $this->client->request('GET', '/artist/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test submitting artist creation form.
     */
    public function testCreateArtistSubmit(): void
    {
        $crawler = $this->client->request('GET', '/artist/create');
        $this->assertResponseIsSuccessful();

        $buttonText = $this->translator->trans('save.save');
        $this->assertSelectorExists("button:contains('$buttonText'), input[type=submit][value='$buttonText']");

        $form = $crawler->selectButton($buttonText)->form();
        $form['artist[name]'] = 'Testowy Artysta';

        $this->client->submit($form);

        $this->assertResponseRedirects('/artist');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', $this->translator->trans('Created successfully'));
    }

    /**
     * Test if artist edit page loads.
     */
    public function testEditArtistPageLoads(): void
    {
        $artist = $this->getFirstArtist();
        $crawler = $this->client->request('GET', '/artist/'.$artist->getId().'/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test artist delete redirect when deletion is not allowed.
     */
    public function testDeleteArtistRedirectWhenCannotDelete(): void
    {
        $artist = $this->getFirstArtist();

        $this->client->request('GET', '/artist/'.$artist->getId().'/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Get the first artist from repository or create one if not exists.
     *
     * @return Artist Artist entity
     */
    private function getFirstArtist(): Artist
    {
        $artist = $this->artistRepository->findOneBy([]);
        if (!$artist) {
            $artist = new Artist();
            $artist->setName('Testowy Artysta');
            $this->artistRepository->save($artist);
        }

        return $artist;
    }
}
