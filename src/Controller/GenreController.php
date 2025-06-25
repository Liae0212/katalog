<?php

/**
 * Genre controller.
 */

namespace App\Controller;

use App\Entity\Genre;
use App\Form\Type\GenreType;
use App\Service\GenreServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\FormType;

/**
 * Class GenreController.
 */
#[Route('/genres')]
class GenreController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param GenreServiceInterface $genreService Genre service
     * @param TranslatorInterface   $translator   Translator
     */
    public function __construct(private readonly GenreServiceInterface $genreService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(name: 'genre_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $pagination = $this->genreService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render('genre/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Genre $genre Genre
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'genre_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(Genre $genre): Response
    {
        return $this->render('genre/show.html.twig', ['genre' => $genre]);
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
        name: 'genre_create',
        methods: 'GET|POST'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): Response
    {
        $genre = new Genre();
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->genreService->save($genre);

            $this->addFlash(
                'success',
                $this->translator->trans('Created successfully')
            );

            return $this->redirectToRoute('genre_index');
        }

        return $this->render(
            'genre/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Genre   $genre   Genre entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'genre_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Genre $genre): Response
    {
        if (!$this->genreService->canBeDeleted($genre)) {
            $this->addFlash(
                'warning',
                $this->translator->trans('Genre contains tasks')
            );

            return $this->redirectToRoute('genre_index');
        }

        $form = $this->createForm(
            FormType::class,
            $genre,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('genre_delete', ['id' => $genre->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->genreService->delete($genre);

            $this->addFlash(
                'success',
                $this->translator->trans('Deleted successfully')
            );

            return $this->redirectToRoute('genre_index');
        }

        return $this->render(
            'genre/delete.html.twig',
            [
                'form' => $form->createView(),
                'genre' => $genre,
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Genre   $genre   Genre entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'genre_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Genre $genre): Response
    {
        $form = $this->createForm(
            GenreType::class,
            $genre,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('genre_edit', ['id' => $genre->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->genreService->save($genre);

            $this->addFlash(
                'success',
                $this->translator->trans('Updated successfully')
            );

            return $this->redirectToRoute('genre_index');
        }

        return $this->render(
            'genre/edit.html.twig',
            [
                'form' => $form->createView(),
                'genre' => $genre,
            ]
        );
    }
}
