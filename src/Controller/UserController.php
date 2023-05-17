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
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class UserController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer la liste des utilisateurs lié au client connecté
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des utilisateurs lié au client connecté",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Users")
     * 
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param TagAwareCacheInterface $cache
     * 
     * @return JsonResponse
     */
    #[Route('/api/users', name: 'client_users', methods: ['GET'])]
    public function getUserFromClient(UserRepository $userRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    { 
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $idCache = 'getUserFromClient_' . $page . '_' . $limit;

        $jsonUsersList = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit, $serializer) {
            $item->tag('usersListCache');

            $usersList = $userRepository->findbyWithPagination($page, $limit, $this->getUser());

            $context = SerializationContext::create()->setGroups(['getUsers']);

            return $serializer->serialize($usersList, 'json', $context);
        });

        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer un utilisateur lié au client connecté
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste d'un utilisateurs lié au client connecté",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Tag(name="Users")
     * 
     * @param User $user
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * 
     * @return JsonResponse
     */
    #[Route('/api/users/{id}', name: 'infos_user', methods: ['GET'])]
    public function getInfosUser(User $user, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        if ($user->getClient() !== $this->getUser()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $idCache = 'getInfosUser_' . $user->getId();

        $jsonUser = $cache->get($idCache, function (ItemInterface $item) use ($user, $serializer) {
            $item->tag('userCache');

            $context = SerializationContext::create()->setGroups(['getUsers']);

            return $serializer->serialize($user, 'json', $context);
        });

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de mettre à jour un utilisateur
     * 
     * @OA\Tag(name="Users")
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param User $currentUser
     * @param EntityManagerInterface $entityManager
     * @param TagAwareCacheInterface $cache
     * @param ValidatorInterface $validator
     * 
     * @return JsonResponse
     */
    #[Route('/api/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer,User $currentUser, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache, ValidatorInterface $validator): JsonResponse
    {
        if ($currentUser->getClient() !== $this->getUser()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $newUser = $serializer->deserialize($request->getContent(), User::class, 'json');

        $currentUser->setFirstname($newUser->getFirstname());
        $currentUser->setLastname($newUser->getLastname());
        $currentUser->setEmail($newUser->getEmail());

        $currentUser->setClient($this->getUser());

        /* check error */
        $errors = $validator->validate($currentUser);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($currentUser);
        $entityManager->flush();

        $cache->invalidateTags(['userCache', 'usersListCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de supprimer un utilisateur
     * 
     * @OA\Tag(name="Users")
     * 
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @param TagAwareCacheInterface $cache
     * 
     * @return JsonResponse
     */
    #[Route('/api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
        if ($user->getClient() !== $this->getUser()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        
        $cache->invalidateTags(['userCache', 'usersListCache']);

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de créer un utilisateur
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne l'utilisateurs lié au client connecté créé",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\RequestBody(
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="firstname",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="lastname",
     *                  type="string"
     *              ),  
     *              @OA\Property(
     *                  property="email",
     *                  type="string"
     *              ),
     *          ),
     *    )
     * )
     *
     * @OA\Tag(name="Users")
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @param TagAwareCacheInterface $cache
     * 
     * @return JsonResponse
     */
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
