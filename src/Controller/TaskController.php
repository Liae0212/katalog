<?php

/**
 * Task controller.
 */

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Comment;
use App\Entity\GuestUser;
use App\Form\Type\TaskType;
use App\Form\Type\CommentType;
use App\Service\CommentService;
use App\Entity\User;
use App\Service\TaskServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class TaskController.
 */
#[Route('/task')]
class TaskController extends AbstractController
{
    public $requestStack;
    public $guestUserService;
    public $taskRepository;
    public $tokenStorage;

    /**
     * Constructor.
     *
     * @param TaskServiceInterface $taskService    Task service
     * @param TranslatorInterface  $translator     Translator
     * @param CommentService       $commentService Comment service
     */
    public function __construct(private readonly TaskServiceInterface $taskService, private readonly TranslatorInterface $translator, private readonly CommentService $commentService)
    {
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'task_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {
        $filters = $this->getFilters($request);
        $pagination = $this->taskService->getPaginatedList(
            $request->query->getInt('page', 1),
            $filters
        );

        return $this->render('task/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Show action.
     *
     * @param Task $task Task entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'task_show', requirements: ['id' => '[1-9]\d*'], methods: 'GET')]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Task    $task    Task entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'task_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('EDIT', subject: 'task')]
    public function edit(Request $request, Task $task): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(
            TaskType::class,
            $task,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl(
                    'task_edit',
                    ['id' => $task->getId()]
                ),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->save($task);
            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('task_index');
        }

        return $this->render(
            'task/edit.html.twig',
            [
                'form' => $form->createView(),
                'task' => $task,
            ]
        );
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
        name: 'task_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $task = new Task();
        $task->setUsers($user);
        $form = $this->createForm(
            TaskType::class,
            $task,
            ['action' => $this->generateUrl('task_create')]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
                $email = $this->requestStack->getSession()->get('email');
                $guestUser = new GuestUser();
                $guestUser->setEmail($email);
                $this->guestUserService->save($guestUser);
            }

            $this->taskService->save($task);

            $this->addFlash('success', $this->translator->trans('message.task_created_successfully'));

            return $this->redirectToRoute('task_index');
        }

        return $this->render(
            'task/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Task    $task    Task entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'task_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('DELETE', subject: 'task')]
    public function delete(Request $request, Task $task): Response
    {
        $form = $this->createForm(
            FormType::class,
            $task,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'task_delete',
                    ['id' => $task->getId()]
                ),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->delete($task);
            $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));

            return $this->redirectToRoute('task_index');
        }

        return $this->render(
            'task/delete.html.twig',
            [
                'form' => $form->createView(),
                'task' => $task,
            ]
        );
    }

    /**
     * View action.
     *
     * @param Request $request HTTP request
     * @param int     $id      Task ID
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'view_task',
        requirements: ['id' => '[0-9]\d*'],
        methods: ['GET', 'POST']
    )]
    public function view(Request $request, int $id): Response
    {
        $task = $this->taskRepository->find($id);
        $pagination = $this->commentService->getPaginatedList($request->query->getInt('page', 1));

        $comment = new Comment();
        $comment->setTask($task);
        $comment->setUser($this->tokenStorage->getToken()->getUser());
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentService->save($comment);

            return $this->redirectToRoute('view_task', ['id' => $id]);
        }

        return $this->render('task/show.html.twig', [
            'task' => $task,
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     *
     * @psalm-return array{category_id: int, tag_id: int, status_id: int}
     */
    private function getFilters(Request $request): array
    {
        return ['category_id' => $request->query->getInt('filters_category_id'), 'tag_id' => $request->query->getInt('filters_tag_id')];
    }
}
