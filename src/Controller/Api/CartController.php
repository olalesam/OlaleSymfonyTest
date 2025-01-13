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

#[Route('/api/products')]
class CartController extends AbstractController
{
    private Security $security;
    private CartRepository $cartRepository;
    private $entityManager; 

    public function __construct(EntityManagerInterface $entityManager,Security $security, CartRepository $cartRepository)
    {
        $this->security = $security;
        $this->cartRepository = $cartRepository;
        $this->entityManager = $entityManager;
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
        // Récupérer l'utilisateur connecté
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Décoder la requête JSON pour récupérer l'ID du produit
        $data = json_decode($request->getContent(), true);

        if (!isset($data['productId'])) {
            return new JsonResponse(['error' => 'ID du produit manquant'], 400);
        }

        // Récupérer le produit
        $product = $this->entityManager->getRepository(Product::class)->find($data['productId']);
        
        if (!$product) {
            return new JsonResponse(['error' => 'Produit non trouvé'], 404);
        }

        // Récupérer le panier de l'utilisateur
        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
        

        if (!$cart) {
            return new JsonResponse(['error' => 'Panier non trouvé'], 404);
        }

        // Vérifier si le produit est dans le panier
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
            return new JsonResponse(['error' => 'Produit non trouvé dans le panier'], 404);
        }
    }
    
    #[Route('/cart/details', name: 'cart_details', methods: ['GET'])]
    public function getCartDetails(EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer l'utilisateur connecté
        $user = $this->security->getUser();  // Récupère l'utilisateur connecté

        // Vérifiez si l'utilisateur est connecté
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }

        $entityManager->initializeObject($user);
        $userId = $user->getId();
       
        // Vérifier si l'utilisateur a déjà un panier

        $cart = $this->cartRepository->findByUserId($userId);

        // Préparer les détails du panier
        $cartDetails = [
            'cart_id' => $cart->getId(),
            'user_id' => $cart->getUser()->getId(),
            'products' => []
        ];
        // Ajouter les détails des produits
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

