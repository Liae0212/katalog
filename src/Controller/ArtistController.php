<?php

/**
 * Artist controller.
 */

namespace App\Controller;

use App\Entity\Artist;
use App\Form\Type\ArtistType;
use App\Service\ArtistServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\FormType;

/**
 * Class ArtistController.
 */
#[Route('/artist')]
class ArtistController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param ArtistServiceInterface $artistService Artist service
     * @param TranslatorInterface    $translator    Translator
     */
    public function __construct(private readonly ArtistServiceInterface $artistService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(name: 'artist_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $pagination = $this->artistService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render('artist/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Artist $artist Artist
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'artist_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(Artist $artist): Response
    {
        return $this->render('artist/show.html.twig', ['artist' => $artist]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'artist_create',
        methods: 'GET|POST',
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): Response
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->artistService->save($artist);

            $this->addFlash(
                'success',
                $this->translator->trans('Created successfully')
            );

            return $this->redirectToRoute('artist_index');
        }

        return $this->render(
            'artist/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Artist  $artist  Artist entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'artist_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Artist $artist): Response
    {
        if (!$this->artistService->canBeDeleted($artist)) {
            $this->addFlash(
                'warning',
                $this->translator->trans('Artist contains tasks')
            );

            return $this->redirectToRoute('artist_index');
        }

        $form = $this->createForm(
            FormType::class,
            $artist,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('artist_delete', ['id' => $artist->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->artistService->delete($artist);

            $this->addFlash(
                'success',
                $this->translator->trans('Deleted successfully')
            );

            return $this->redirectToRoute('artist_index');
        }

        return $this->render(
            'artist/delete.html.twig',
            [
                'form' => $form->createView(),
                'artist' => $artist,
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Artist  $artist  Artist entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'artist_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Artist $artist): Response
    {
        $form = $this->createForm(
            ArtistType::class,
            $artist,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('artist_edit', ['id' => $artist->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->artistService->save($artist);

            $this->addFlash(
                'success',
                $this->translator->trans('Created successfully')
            );

            return $this->redirectToRoute('artist_index');
        }

        return $this->render(
            'artist/edit.html.twig',
            [
                'form' => $form->createView(),
                'artist' => $artist,
            ]
        );
    }
}
