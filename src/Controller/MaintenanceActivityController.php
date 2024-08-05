<?php


namespace App\Controller;

use App\Entity\MaintenanceActivity;
use App\Repository\MaintenanceActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MaintenanceActivityController extends AbstractController
{
    private $entityManager;
    private $maintenanceActivityRepository;
    private $serializer;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, MaintenanceActivityRepository $maintenanceActivityRepository, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->maintenanceActivityRepository = $maintenanceActivityRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/api/maintenance-activities', methods: ['GET'])]
    public function getAllMaintenanceActivities(): JsonResponse
    {
        $activities = $this->maintenanceActivityRepository->findAll();
        $data = $this->serializer->serialize($activities, 'json', ['groups' => 'maintenance_activity:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/maintenance-activities/{id}', methods: ['GET'])]
    public function getMaintenanceActivity(int $id): JsonResponse
    {
        $activity = $this->maintenanceActivityRepository->find($id);
        if (!$activity) {
            return new JsonResponse(['error' => 'Maintenance activity not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $this->serializer->serialize($activity, 'json', ['groups' => 'maintenance_activity:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/maintenance-activities', methods: ['POST'])]
    public function createMaintenanceActivity(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $activity = $this->serializer->deserialize($data, MaintenanceActivity::class, 'json');
        $errors = $this->validator->validate($activity);
        if (count($errors) > 0) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }
        $this->entityManager->persist($activity);
        $this->entityManager->flush();
        $data = $this->serializer->serialize($activity, 'json', ['groups' => 'maintenance_activity:read']);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/maintenance-activities/{id}', methods: ['PUT'])]
    public function updateMaintenanceActivity(Request $request, int $id): JsonResponse
    {
        $activity = $this->maintenanceActivityRepository->find($id);
        if (!$activity) {
            return new JsonResponse(['error' => 'Maintenance activity not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $request->getContent();
        $updatedActivity = $this->serializer->deserialize($data, MaintenanceActivity::class, 'json');
        $activity->setName($updatedActivity->getName())
            ->setDescription($updatedActivity->getDescription())
            ->setDate($updatedActivity->getDate())
            ->setCar($updatedActivity->getCar())
            ->setOwner($updatedActivity->getOwner());

        $errors = $this->validator->validate($activity);
        if (count($errors) > 0) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }
        $this->entityManager->flush();
        $data = $this->serializer->serialize($activity, 'json', ['groups' => 'maintenance_activity:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/maintenance-activities/{id}', methods: ['DELETE'])]
    public function deleteMaintenanceActivity(int $id): JsonResponse
    {
        $activity = $this->maintenanceActivityRepository->find($id);
        if (!$activity) {
            return new JsonResponse(['error' => 'Maintenance activity not found'], Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($activity);
        $this->entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
