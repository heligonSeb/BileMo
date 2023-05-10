<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'client_users', methods: ['GET'])]
    public function getUserFromClient(UserRepository $userRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    { 
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $idCache = 'getUserFromClient_' . $page . '_' . $limit;

        $jsonUsersList = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit, $serializer) {
            echo('ELEM NOT IN CACHE YET !\n');
            
            $item->tag('usersListCache');

            $usersList = $userRepository->findbyWithPagination($page, $limit, $this->getUser());

            $context = SerializationContext::create()->setGroups(['getUsers']);

            return $serializer->serialize($usersList, 'json', $context);
        });

        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'infos_user', methods: ['GET'])]
    public function getInfosUser(User $user, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idCache = 'getInfosUser_' . $user->getId();

        $jsonUser = $cache->get($idCache, function (ItemInterface $item) use ($user, $serializer) {
            $item->tag('userCache');

            $context = SerializationContext::create()->setGroups(['getUsers']);

            return $serializer->serialize($user, 'json', $context);
        });

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer,User $currentUser, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache, ValidatorInterface $validator): JsonResponse
    {
        $newUser = $serializer->deserialize($request->getContent(), User::class, 'json');

        $currentUser->setFirstname($newUser->getFirstname());
        $currentUser->setLastname($newUser->getLastname());
        $currentUser->setEmail($newUser->getEmail());

        $currentUser->setClient($this->getUser());

        /* check error */
        $errors = $validator->validate($currentUser);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
            
            // throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requete est invalide");
        }

        $entityManager->persist($currentUser);
        $entityManager->flush();

        $cache->invalidateTags(['userCache', 'usersListCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(['userCache', 'usersListCache']);

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {    
        
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setClient($this->getUser());
        
        /* check error */
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
            
            // throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requete est invalide");
        }
        
        $entityManager->persist($user);
        $entityManager->flush();
        
        $cache->invalidateTags(['usersListCache']);
        
        $idCache = 'createUser_' . $user->getId();

        $jsonUser = $cache->get($idCache, function (ItemInterface $item) use ($user, $serializer) {
            $item->tag('userCache');

            $context = SerializationContext::create()->setGroups(['getUsers']);

            return $serializer->serialize($user, 'json', $context);
        });

        $infosUser = $urlGenerator->generate('infos_user', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["infos user" => $infosUser], true);
    }
}
