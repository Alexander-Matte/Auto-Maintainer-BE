<?php


namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CarController extends AbstractController
{
    private $entityManager;
    private $carRepository;
    private $serializer;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, CarRepository $carRepository, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->carRepository = $carRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/api/cars', methods: ['GET'])]
    public function getAllCars(): JsonResponse
    {
        $cars = $this->carRepository->findAll();
        $data = $this->serializer->serialize($cars, 'json', ['groups' => 'car:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/cars/{id}', methods: ['GET'])]
    public function getCar(int $id): JsonResponse
    {
        $car = $this->carRepository->find($id);
        if (!$car) {
            return new JsonResponse(['error' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $this->serializer->serialize($car, 'json', ['groups' => 'car:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/cars', methods: ['POST'])]
    public function createCar(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $car = $this->serializer->deserialize($data, Car::class, 'json');
        $errors = $this->validator->validate($car);
        if (count($errors) > 0) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }
        $this->entityManager->persist($car);
        $this->entityManager->flush();
        $data = $this->serializer->serialize($car, 'json', ['groups' => 'car:read']);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/cars/{id}', methods: ['PUT'])]
    public function updateCar(Request $request, int $id): JsonResponse
    {
        $car = $this->carRepository->find($id);
        if (!$car) {
            return new JsonResponse(['error' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $request->getContent();
        $updatedCar = $this->serializer->deserialize($data, Car::class, 'json');
        $car->setYear($updatedCar->getYear())
            ->setMake($updatedCar->getMake())
            ->setModel($updatedCar->getModel());

        $errors = $this->validator->validate($car);
        if (count($errors) > 0) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }
        $this->entityManager->flush();
        $data = $this->serializer->serialize($car, 'json', ['groups' => 'car:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/cars/{id}', methods: ['DELETE'])]
    public function deleteCar(int $id): JsonResponse
    {
        $car = $this->carRepository->find($id);
        if (!$car) {
            return new JsonResponse(['error' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($car);
        $this->entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

