<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'client_users')]
    public function getUserFromClient(UserRepository $userRepository, SerializerInterface $serializerInterface): JsonResponse
    { 
        $usersList = $userRepository->findby(['Client' => $this->getUser()]);

        $jsonUsersList = $serializerInterface->serialize($usersList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }
}
