<?php

namespace App\Controller;

use App\Entity\Result;
use App\Repository\ResultRepository;
use App\Repository\TaskRepository;
use App\Service\TaskAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TaskController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TaskRepository $taskRepository,
        protected ResultRepository $resultRepository,
        protected TaskAccessService $taskAccessService,
        protected Security $security,
    ) {}

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/tasks', name: 'tasks_list', methods: ['GET'], format: 'json')]
    public function getTasks(): JsonResponse
    {
        $tasks = $this->taskRepository->findAll();
        
        return $this->json(data: [
            'tasks' => $tasks,
        ], context: [ 'groups' => 'task' ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/tasks/{id}', name: 'tasks_by_id', methods: ['GET'], format: 'json', requirements: ['id' => Requirement::UUID])]
    public function getSingleTask(string $id): JsonResponse
    {
        $task = $this->taskRepository->findOneById($id);

        return $this->json(data: [
            'task' => $task,
        ], context: [ 'groups' => 'task' ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/tasks/reservations', name: 'tasks_reservations_list', methods: ['GET'], format: 'json')]
    public function getReservations(): JsonResponse
    {
        $user = $this->getUser();
        $tasks = $this->taskRepository->findReservedByUser($user);

        return $this->json(data: [
            'tasks' => $tasks,
        ], context: [ 'groups' => 'reservation' ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/tasks/reservations/{id}', name: 'tasks_reservations_by_id', methods: ['GET'], format: 'json', requirements: ['id' => Requirement::UUID])]
    public function getSingleReservation(string $id): JsonResponse
    {
        $task = $this->taskRepository->findOneById($id);
        $this->taskAccessService->canAccessTaskForRead($task);

        return $this->json(data: [
            'task' => $task,
        ], context: [ 'groups' => 'reservation' ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/tasks/results/{id}', name: 'tasks_results_by_id', methods: ['GET'], requirements: ['id' => Requirement::UUID])]
    public function getResultContent(string $id): Response
    {
        $result = $this->resultRepository->findOneById($id);
        return new Response(stream_get_contents($result->getContent()), Response::HTTP_OK, [
            'Content-Type' => $result->getMimeType(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/tasks/reservations', name: 'tasks_reservations_new', methods: ['POST'], format: 'json')]
    public function makeReservation(): JsonResponse
    {
        // Użytkownik przy rezerwacji powinien widzieć tylko paramsy… ale tez id
        // TODO: Limit jednoczesnych rezerwacji?
        $task = $this->taskRepository->getOneFree();
        if (!$task) {
            throw new ConflictHttpException('There is no free task to reserve');
        }

        $user = $this->getUser();
        $task->setUser($user);
        $task->setReservedAt(new \DateTime);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json([
            'task' => $task,
        ], Response::HTTP_CREATED, context: [ 'groups' => 'reservation' ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/tasks/{id}/results', name: 'tasks_results_new', methods: ['POST'], format: 'json', requirements: ['id' => Requirement::UUID])]
    public function addResult(Request $request, string $id): JsonResponse
    {
        $task = $this->taskRepository->findOneById($id);
        $this->taskAccessService->canAccessTaskForWrite($task);

        $result = new Result();
        $result->setTask($task);
        if ($request->files->has('file')) {
            $file = $request->files->get('file');
            $stream = fopen($file->getPathname(), 'rb');
            $result->setContent($stream);
            $result->setMimeType($file->getMimeType());
        } else {
            $mimeType = $request->headers->get('Content-Type');
            if (!$mimeType) {
                throw new BadRequestHttpException('Missing content');
            }

            $result->setContent($request->getContent(true));
            $result->setMimeType($mimeType);
        }

        $this->entityManager->persist($result);
        $this->entityManager->flush();

        return $this->json([
            'result' => $result,
        ], Response::HTTP_CREATED, context: [ 'groups' => 'result' ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/tasks/reservations/{id}', name: 'tasks_reservations_delete', methods: ['DELETE'], format: 'json', requirements: ['id' => Requirement::UUID])]
    public function finishReservation(Request $request, string $id): JsonResponse
    {
        // TODO: obawiam się pierdolnika statusowego, może dobrze byłoby dodać jakąś kolumnę na status, a te kolumny traktować poglądowo?
        if (!$request->query->has('finished')) {
            throw new BadRequestHttpException('Missing "finished" query parameter');
        }

        $finished = (bool)$request->query->get('finished');
        $task = $this->taskRepository->findOneById($id);
        $this->taskAccessService->canAccessTaskForWrite($task);

        if ($finished) {
            $task->setFinishedAt(new \DateTime);
        } else {
            $task->setUser(null);
            $task->setReservedAt(null);
            foreach ($task->getResults() as $result) {
                $this->entityManager->remove($result);
            }
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();
        return $this->json([]);
    }
}
