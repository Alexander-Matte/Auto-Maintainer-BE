<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    private $serializer;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/api/users', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        $data = $this->serializer->serialize($users, 'json', ['groups' => 'user:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', methods: ['GET'])]
    public function fetchUser(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users', methods: ['POST'])]
    public function createUser(Request $request, RegistrationController $registrationController): JsonResponse
    {
        return $registrationController->register($request);
    }

    #[Route('/api/users/{id}', methods: ['PUT'])]
    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $request->getContent();
        $updatedUser = $this->serializer->deserialize($data, User::class, 'json');
        $user->setEmail($updatedUser->getEmail())
            ->setRoles($updatedUser->getRoles())
            ->setPassword($updatedUser->getPassword());

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }
        $this->entityManager->flush();
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
