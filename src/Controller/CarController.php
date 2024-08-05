<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CarController extends AbstractController
{
    #[Route('/api/cars', name: 'get_cars', methods: ['GET'])]
    public function getCars(CarRepository $carRepository): JsonResponse
    {
        $cars = $carRepository->findAll();
        return $this->json($cars);
    }

    #[Route('/api/cars/{id}', name: 'get_car', methods: ['GET'])]
    public function getCar(Car $car): JsonResponse
    {
        return $this->json($car);
    }

    #[Route('/api/cars', name: 'create_car', methods: ['POST'])]
    public function createCar(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $car = new Car();
        $car->setYear($data['year']);
        $car->setMake($data['make']);
        $car->setModel($data['model']);
        $car->setUser($this->getUser()); // Assuming the user is authenticated and set
        $em->persist($car);
        $em->flush();

        return $this->json($car, Response::HTTP_CREATED);
    }

    #[Route('/api/cars/{id}', name: 'update_car', methods: ['PUT'])]
    public function updateCar(Request $request, Car $car, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $car->setYear($data['year'] ?? $car->getYear());
        $car->setMake($data['make'] ?? $car->getMake());
        $car->setModel($data['model'] ?? $car->getModel());
        $em->flush();

        return $this->json($car);
    }

    #[Route('/api/cars/{id}', name: 'delete_car', methods: ['DELETE'])]
    public function deleteCar(Car $car, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($car);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
