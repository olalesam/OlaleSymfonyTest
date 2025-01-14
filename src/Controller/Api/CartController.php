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

#[Route('/api/products')]
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

        
        $user = $this->security->getUser();  // Récupère l'utilisateur connecté

        // Vérifiez si l'utilisateur est connecté
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        
        // Vérifiez si le produit existe
        $product = $entityManager->getRepository(Product::class)->find($data['productId']);
        if (!$product) {
            return new JsonResponse(['error' => 'Produit non trouvé'], 404);
        }

        // Vérifier si l'utilisateur a déjà un panier
        //$cart = $user->getCarts()->first();
        
        
        $entityManager->initializeObject($user);
        $userId = $user->getId();
       
        // Vérifier si l'utilisateur a déjà un panier

        $cart = $this->cartRepository->findByUserId($userId);
    
        if (!$cart) {
            // Si l'utilisateur n'a pas de panier, créer un nouveau panier
            $cart = new Cart();
            $cart->setUser($user);
            $user->addCart($cart);
        }

        try {
            //dump($product);
            $cart->addProduct($product);
            $entityManager->persist($cart);
            $entityManager->flush();
            return $this->json([
                
                'status' => 'success',
                'message' => 'Product added successfully.',
            ]);
        } 
        catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
        
        // Ajouter le produit au panier
        

        return new JsonResponse(['message' => 'Produit ajouté au panier']);
        
        
    }

    #[Route('/deleteCart', name: 'delete_cart', methods: ['POST'])]
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
                // Supprimer le produit du panier
                $cart->getProducts()->removeElement($product);
                // Persister les changements dans la base de données
                $this->entityManager->flush();
                return $this->json([
                    
                    'status' => 'success',
                    'message' => 'Produit supprimé du panier',
                ]);
            } 
            catch (\Exception $e) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Error: ' . $e->getMessage(),
                ]);
            } 

        } else {
            return JsonResponseHelper::forbidden('Product not found in cart');
        }
    }
    
    #[Route('/cart/details', name: 'cart_details', methods: ['GET'])]
    public function getCartDetails(EntityManagerInterface $entityManager): JsonResponse
    {
    
        if (!$this->user instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
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

