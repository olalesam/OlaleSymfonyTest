<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use App\Service\JsonResponseHelper;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    private function isAdmin(): bool
    {
        $user = $this->security->getUser();
        return $user instanceof User && $user->getEmail() === 'admin@admin.com';
    }
 
    #[Route('/new', name: 'product_new', methods: ['POST'])]
    public function addProduct(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // addProduct
        if (!$this->isAdmin()) {
            return JsonResponseHelper::unauthorized('Access denied');
        }
        // create product entity
        $data = json_decode($request->getContent(), true);

        // checking fields
        $requiredFields = ['code', 'name', 'category', 'price', 'quantity', 'internalReference', 'inventoryStatus'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->json([
                    'status' => 'error',
                    'message' => "The field '$field' is required and cannot be empty.",
                ], Response::HTTP_BAD_REQUEST);
            }
        }
        
        $product = new Product();

        // Validation
        $product->setCode($data['code']);
        $product->setName($data['name']);
        $product->setImage(isset($data['image']) ? $data['image'] : null);
        $product->setDescription(isset($data['description']) ? $data['description'] : null);
        $product->setCategory($data['category'] ? $data['category'] : null);
        $product->setPrice($data['price'] ? $data['price'] : 0);
        $product->setQuantity($data['quantity'] ? $data['quantity'] : 0);
        $product->setInternalReference($data['internalReference'] ? $data['internalReference'] : null);
        $product->setInventoryStatus($data['inventoryStatus'] ? $data['inventoryStatus'] : null);
        $product->setShellId(isset($data['shellId']) ? $data['shellId'] : 0);
        $product->setRating(isset($data['rating']) ? $data['rating'] : 0);
        $product->onPrePersist();

        // Validation inputs parameters
        $errors = $validator->validate($product);

        if (count($errors) > 0) { // Show errors messages
            return $this->json([
                'status' => 'error',
                'message' => (string) $errors,
            ]);
        }

        try {
            $entityManager->persist($product);
            $entityManager->flush();
            return JsonResponseHelper::success([], 'product add success');
        } 
        catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        } 
    }

    #[Route('/{id}/edit', name: 'product_edit', methods: ['PUT'])]
    public function editProduct(
        int $id, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        ValidatorInterface $validator
    ): Response {
        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            return JsonResponseHelper::forbidden('Product not found.');
        }

        if (!$this->isAdmin()) {
            return JsonResponseHelper::unauthorized('Access denied');
        }

        $data = json_decode($request->getContent(), true);

        // check if code is using by another product
        if (isset($data['code']) && $data['code'] !== $product->getCode()) {
            $existingProduct = $entityManager->getRepository(Product::class)->findOneBy(['code' => $data['code']]);
            if ($existingProduct) {
                return JsonResponseHelper::error('The code is already in use by another product.');
            }
        }

        // updating fields
        $product->setName($data['code'] ?? $product->getCode());
        $product->setName($data['name'] ?? $product->getName());
        $product->setImage($data['image'] ?? null);
        $product->setDescription($data['description'] ?? null);
        $product->setCategory($data['category'] ?? null);
        $product->setPrice($data['price'] ?? 0);
        $product->setQuantity($data['quantity'] ?? 0);
        $product->setInternalReference($data['internalReference'] ?? null);
        $product->setInventoryStatus($data['inventoryStatus'] ?? null);
        $product->setShellId($data['shellId'] ?? 0);
        $product->setRating($data['rating'] ?? 0);
        $product->onPrePersist();

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            return JsonResponseHelper::error((string) $errors);
        }

        $entityManager->flush();
        return JsonResponseHelper::success([], 'Product updated successfully');
    }


    #[Route('/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function deleteProduct(int $id, 
    EntityManagerInterface $entityManager, 
    ): Response
    {
        if (!$this->isAdmin()) {
            return JsonResponseHelper::unauthorized('Access denied.');
        }

        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            return JsonResponseHelper::forbidden('Product not found.');
        }

        $entityManager->remove($product);
        $entityManager->flush();
        return JsonResponseHelper::success([],'Product deleted successfully');
    }


}
