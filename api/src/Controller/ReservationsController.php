<?php

namespace App\Controller;

use App\Entity\Result;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ReservationsController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {}

    #[Route('/reservations', name: 'reservations_list', methods: ['GET'], format: 'json')]
    public function getUserReservations(): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $tasks = $this->entityManager->getRepository(Task::class)->findReservedByUser($user);
        return $this->json(data: [
            'tasks' => $tasks,
        ], context: [ 'groups' => 'task' ]);
    }

    #[Route('/reservations/{id}', name: 'reservation_single', methods: ['GET'], format: 'json')]
    public function getSingleReservation(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $task = $this->getReservation($request);

        return $this->json(data: [
            'task' => $task,
        ], context: [ 'groups' => 'task' ]);
    }

    #[Route('/reservations/{id}/results', name: 'reservation_results', methods: ['GET'], format: 'json')]
    public function getSingleReservationResults(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $task = $this->getReservation($request);
        $resultIds = $this->entityManager->getRepository(Result::class)->findAllIdsByTask($task);

        return $this->json([
            'results' => $resultIds,
        ]);
    }


    #[Route('/reservations', name: 'reservations_new', methods: ['POST'], format: 'json')]
    public function makeReservation(): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $task = $this->entityManager->getRepository(Task::class)->getOneFree();
        if (!$task) {
            throw new ConflictHttpException('There is no free task to reserve');
        }

        $task->setUser($user);
        $task->setReservedAt(new \DateTime);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json(
            data: [
                'task' => $task,
            ],
            status: Response::HTTP_CREATED,
            context: [ 'groups' => 'task' ]
        );
    }

    #[Route('/reservations/{id}/results', name: 'reservation_results_add', methods: ['POST'], format: 'json')]
    public function addResult(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $task = $this->getReservation($request);
        $body = json_decode($request->getContent(), true);

        if ($task->getFinishedAt() !== null) {
            throw new BadRequestHttpException('Could not add task to previously finished reservation');
        }

        $result = new Result();
        $result->setTask($task);
        $result->setData($body);
        $this->entityManager->persist($result);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Successfully added result',
        ]);
    }

    #[Route('/reservations/{id}', name: 'reservations_finish', methods: ['DELETE'], format: 'json')]
    public function finishReservation(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $task = $this->getReservation($request);

        if ($task->getFinishedAt() !== null) {
            throw new BadRequestHttpException('Reservation has been already finished');
        }

        $task->setFinishedAt(new \DateTime);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Successfully finished reservation',
        ]);
    }

    private function getTask(Request $request): Task
    {
        $id = $request->get('id');

        if (!UuidV4::isValid($id)) {
            throw new BadRequestHttpException("Value '$id' is not a valid UUID");
        }

        $task = $this->entityManager->getRepository(Task::class)->findOneById($id);
        if (!$task) {
            throw new NotFoundHttpException("Task with '$id' does not exist");
        }

        return $task;
    }

    private function getReservation(Request $request): Task
    {
        $task = $this->getTask($request);
        $user = $this->getUser();

        if ($task->getUser() !== $user) {
            throw new AccessDeniedHttpException("It's not your reservation");
        }
   
        return $task;
    }
}
