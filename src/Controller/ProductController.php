<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 4);

        $idCache = 'getAllProducts_' . $page . '_' . $limit;

        $jsonProductsList = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit, $serializer) {
            echo('ELEM NOT IN CACHE YET !\n');
            $item->tag('productsListCache');

            $productsList = $productRepository->findAllWithPagination($page, $limit);

            return $serializer->serialize($productsList, 'json');
        });

        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'product', methods: ['GET'])]
    public function getProduct(Product $product, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idCache = 'getProduct_' . $product->getId();

        $jsonProduct = $cache->get($idCache, function (ItemInterface $item) use ($product, $serializer) {
            $item->tag('productCache');

            return $serializer->serialize($product, 'json');
        });

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
