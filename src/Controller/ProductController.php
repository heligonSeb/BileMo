<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $productsList = $productRepository->findAll();

        $jsonProductsList = $serializerInterface->serialize($productsList, 'json');

        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'product', methods: ['GET'])]
    public function getProduct(Product $product, SerializerInterface $serializerInterface): JsonResponse
    {
        $jsonProduct = $serializerInterface->serialize($product, 'json');

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);


        // $product = $productRepository->find($id);

        // if ($product) {
        //     $jsonProduct = $serializerInterface->serialize($product, 'json');

        //     return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        // }

        // return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
