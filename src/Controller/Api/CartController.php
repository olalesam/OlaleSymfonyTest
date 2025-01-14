<?php

namespace App\Controller\Api;
// src/Controller/CartController.php

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CartRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;
use App\Service\JsonResponseHelper;

#[Route('/api/cart')]
class CartController extends AbstractController
{
    private Security $security;
    private CartRepository $cartRepository;
    private $entityManager; 
    private USer $user;

    public function __construct(EntityManagerInterface $entityManager,Security $security, CartRepository $cartRepository)
    {
        $this->security = $security;
        $this->cartRepository = $cartRepository;
        $this->entityManager = $entityManager;
        $this->user = $this->security->getUser();
    }
    
    #[Route('/add', name: 'cart_add', methods: ['POST'])]
    public function addToCart(Request $request, 
    EntityManagerInterface $entityManager): JsonResponse
    {

        // checking if user is connected
        if (!$this->user instanceof User) {
            return JsonResponseHelper::unauthorized('user not identify');
        }

        $data = json_decode($request->getContent(), true);
        
        // checking if product exist
        $product = $entityManager->getRepository(Product::class)->find($data['productId']);
        if (!$product) {
            return JsonResponseHelper::forbidden('Product not found');
        }
        
        $entityManager->initializeObject($this->user);
        $userId = $this->user->getId();
       
        // checking fid user have cart

        $cart = $this->cartRepository->findByUserId($userId);
    
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($this->user);
            $this->user->addCart($cart);
        }

        try {
            $cart->addProduct($product);
            $entityManager->persist($cart);
            $entityManager->flush();
            return JsonResponseHelper::success([],'Product added successfully.');
        } 
        catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
        
        // add product in cart
        
        return JsonResponseHelper::success([],'Product added successfully in cart.');    
    }

    #[Route('/delete', name: 'delete_cart', methods: ['POST'])]
    public function removeFromCart(Request $request): JsonResponse
    {
        if (!$this->user) {
            return JsonResponseHelper::unauthorized('user not identify');
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['productId'])) {
            return JsonResponseHelper::forbidden('ID not found');
        }

        // get product
        $product = $this->entityManager->getRepository(Product::class)->find($data['productId']);
        
        if (!$product) {
            return JsonResponseHelper::forbidden('Product not found');
        }

        // get cart of user
        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $this->user]);
        

        if (!$cart) {
            return JsonResponseHelper::forbidden('Cart not found');
        }

        // checking if product exist in cart
        if ($cart->getProducts()->contains($product)) {
            
            try {
                $cart->getProducts()->removeElement($product);

                $this->entityManager->flush();
                return JsonResponseHelper::success([],'Product removed on cart');
            } 
            catch (\Exception $e) {
                return JsonResponseHelper::error('Error: ' . $e->getMessage());
            } 

        } else {
            return JsonResponseHelper::forbidden('Product not found in cart');
        }
    }
    
    #[Route('/details', name: 'cart_details', methods: ['GET'])]
    public function getCartDetails(EntityManagerInterface $entityManager): JsonResponse
    {
    
        if (!$this->user instanceof User) {
            return JsonResponseHelper::unauthorized('user not identify');
        }

        $entityManager->initializeObject($this->user);
        $userId = $this->user->getId();
       
        // check if user have Cart

        $cart = $this->cartRepository->findByUserId($userId);

        $cartDetails = [
            'cart_id' => $cart->getId(),
            'user_id' => $cart->getUser()->getId(),
            'products' => []
        ];
        // add cart details 
        foreach ($cart->getProducts() as $product) {
            $cartDetails['products'][] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ];
        }

        return new JsonResponse($cartDetails);
    }
}

