<?php

namespace App\Controller;

use App\Entity\MaintenanceActivity;
use App\Repository\MaintenanceActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceActivityController extends AbstractController
{
    #[Route('/api/maintenance-activities', name: 'get_maintenance_activities', methods: ['GET'])]
    public function getMaintenanceActivities(MaintenanceActivityRepository $activityRepository): JsonResponse
    {
        $activities = $activityRepository->findAll();
        return $this->json($activities);
    }

    #[Route('/api/maintenance-activities/{id}', name: 'get_maintenance_activity', methods: ['GET'])]
    public function getMaintenanceActivity(MaintenanceActivity $activity): JsonResponse
    {
        return $this->json($activity);
    }

    #[Route('/api/maintenance-activities', name: 'create_maintenance_activity', methods: ['POST'])]
    public function createMaintenanceActivity(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $activity = new MaintenanceActivity();
        $activity->setName($data['name']);
        $activity->setDescription($data['description']);
        $activity->setDate(new \DateTime($data['date']));
        $activity->setCar($em->getRepository(Car::class)->find($data['car_id']));
        $em->persist($activity);
        $em->flush();

        return $this->json($activity, Response::HTTP_CREATED);
    }

    #[Route('/api/maintenance-activities/{id}', name: 'update_maintenance_activity', methods: ['PUT'])]
    public function updateMaintenanceActivity(Request $request, MaintenanceActivity $activity, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $activity->setName($data['name'] ?? $activity->getName());
        $activity->setDescription($data['description'] ?? $activity->getDescription());
        $activity->setDate(new \DateTime($data['date']) ?? $activity->getDate());
        $activity->setCar($em->getRepository(Car::class)->find($data['car_id']) ?? $activity->getCar());
        $em->flush();

        return $this->json($activity);
    }

    #[Route('/api/maintenance-activities/{id}', name: 'delete_maintenance_activity', methods: ['DELETE'])]
    public function deleteMaintenanceActivity(MaintenanceActivity $activity, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($activity);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

