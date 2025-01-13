<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Entity\Wishlist;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Repository\WishlistRepository;
use App\Service\JsonResponseHelper;
use App\Service\UserService;



#[Route('/api/wishlist')]
class WishlistController extends AbstractController
{

    private Security $security;
    private WishlistRepository $wishlistRepository;
    private $entityManager; 
    private USer $user;

    
    public function __construct(EntityManagerInterface $entityManager,Security $security, WishlistRepository $wishlistRepository)
    {
        $this->security = $security;
        $this->wishlistRepository = $wishlistRepository;
        $this->entityManager = $entityManager;
        $this->user = $this->security->getUser() ; // get user connected
    }

    #[Route('/', name: 'wishlist_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->user instanceof User) {
            return JsonResponseHelper::unauthorized('user not identify');
        }
        $entityManager->initializeObject($this->user);

        $wishlists = $this->user->getWishlists();
        
        $wishlistData = [];
        foreach ($wishlists as $wishlist) {
            $wishlistData[] = [
                'id' => $wishlist->getId(),
                'user_id' => $wishlist->getUser()->getId(),
                'products' => array_map(fn($product) => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice()
                ], $wishlist->getProducts()->toArray())
            ];
        }

        return new JsonResponse($wishlistData);
    }

    #[Route('/add', name: 'wishlist_add', methods: ['POST'])]
    public function addToWishlist(Request $request, 
                                  EntityManagerInterface $entityManager): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        //$user = $this->security->getUser();  // Récupère l'utilisateur connecté

        // Vérifiez si l'utilisateur est connecté
        if (!$this->user instanceof User) {
            return JsonResponseHelper::unauthorized('user not identify');
        }

        // Vérifiez si le produit existe
        $product = $entityManager->getRepository(Product::class)->find($data['productId']);
        if (!$product) {
            return JsonResponseHelper::forbidden('Product not fount');
        }

        $entityManager->initializeObject($this->user);
        //$userId = $user->getId();

        // Crée une nouvelle wishlist s'il n'en a pas
        if ($this->user->getWishlists()->isEmpty()) {
            $wishlist = new Wishlist();
            $this->user->addWishlist($wishlist);
        } else {
            $wishlist = $this->user->getWishlists()->first();
        }

        $wishlist->addProduct($product);
        $entityManager->persist($wishlist);
        $entityManager->flush();
        return JsonResponseHelper::success(['id' => $product], 'Product is saved');
    }

    #[Route('/delete/{productId}', name: 'wishlist_remove', methods: ['DELETE'])]
    public function removeFromWishlist(int $productId): JsonResponse
    {

        if (!$this->user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }

        if ($productId === null) {
            return new JsonResponse(['error' => 'Product ID is required'], 400);
        }

        // Récupérer le produit
        $product = $this->entityManager->getRepository(Product::class)->find($productId);
        
        if (!$product) {
            return new JsonResponse(['error' => 'Produit non trouvé'], 404);
        }

        // Récupérer le panier de l'utilisateur
        $wishlist = $this->entityManager->getRepository(Wishlist::class)->findOneBy(['user' => $this->user]);
        
        if (!$wishlist) {
            return new JsonResponse(['error' => 'Aucune liste d\'envie trouvée'], 404);
        }

        if ($wishlist->getProducts()->contains($product)) {   // check if product exist in the Wishlist
            
            try {
                // Supprimer le produit du panier
                $wishlist->getProducts()->removeElement($product); // delete product to Wishlist 
                
                $this->entityManager->flush();  // Persist data
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
            return new JsonResponse(['error' => 'Produit non trouvé dans le wishlist'], 404);
        }
    }
}
