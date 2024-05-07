<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Task;
use App\Normalizer\TaskNormalizer;
use App\Normalizer\TaskDenormalizer;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class TaskController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getTasks(ManagerRegistry $doctrine): Response {
      $tasks = $doctrine->getRepository(Task::class)->findAll();
      // Assuming that $serializer is injected properly as SerializerInterface
      $data = $this->container->get('serializer')->serialize($tasks, 'json');

      return new JsonResponse($data, Response::HTTP_OK, [], true);
  }

  public function createTask(Request $request, ManagerRegistry $doctrine, SerializerInterface $serializer): Response {
      $jsonContent = $request->getContent();
      $entityManager = $doctrine->getManager();

      try {
          // Directly use the injected serializer to deserialize the Task object
          $task = $this->serializer->deserialize($jsonContent, Task::class, 'json');

          // After deserialization, make sure to convert date strings to DateTime objects if needed
          if ($task->getDeadline() && is_string($task->getDeadline())) {
              $deadlineDateTime = new \DateTime($task->getDeadline());
              $task->setDeadline($deadlineDateTime);
          }

          $entityManager->persist($task);
          $entityManager->flush();

          return new JsonResponse('Task created successfully', Response::HTTP_CREATED);
      } catch (\Exception $e) {
          return new Response('Error creating task: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
      }
  }

  public function getTask($id, ManagerRegistry $doctrine): Response {
      $task = $doctrine->getRepository(Task::class)->find($id);

      if (!$task) {
          return new JsonResponse('No task found for id ' . $id, Response::HTTP_NOT_FOUND);
      }

      $data = $this->container->get('serializer')->serialize($task, 'json');

      return new JsonResponse($data, Response::HTTP_OK, [], true);
  }

  public function updateTask($id, Request $request, ManagerRegistry $doctrine, SerializerInterface $serializer): Response {
      $entityManager = $doctrine->getManager();
      $task = $entityManager->getRepository(Task::class)->find($id);

      if (!$task) {
          return new JsonResponse('No task found for id ' . $id, Response::HTTP_NOT_FOUND);
      }

      $data = json_decode($request->getContent(), true);

      foreach ($data as $key => $value) {
          if (property_exists($task, $key)) {
              $setter = 'set' . ucfirst($key);
              if (method_exists($task, $setter)) {
                  // Converting string to DateTime interface
                  if ($key === 'deadline' && $value !== null) {
                      try {
                          $value = new \DateTime($value);
                      } catch (\Exception $e) {
                          return new JsonResponse('Invalid datetime format', Response::HTTP_BAD_REQUEST);
                      }
                  }

                  // Call the setter method
                  $task->$setter($value);
              }
          }
      }

      $entityManager->flush();

      return new JsonResponse('Task updated successfully', Response::HTTP_OK);
  }

  public function deleteTask($id, ManagerRegistry $doctrine): Response {
      $entityManager = $doctrine->getManager();
      $task = $entityManager->getRepository(Task::class)->find($id);

      if (!$task) {
          return new JsonResponse('No task found for id ' . $id, Response::HTTP_NOT_FOUND);
      }

      $entityManager->remove($task);
      $entityManager->flush();

      return new JsonResponse('Task deleted successfully', Response::HTTP_OK);
  }

}
