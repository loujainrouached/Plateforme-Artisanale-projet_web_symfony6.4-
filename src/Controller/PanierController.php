<?php

namespace App\Controller;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Cart;
use App\Repository\ProductRepository;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(): Response
    {
        return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
        ]);
    }
    
  
  #[Route('/panier/show', name: 'panier_show')]
public function showPanier(CartRepository $cartRepository): Response
{
    // Récupérer l'utilisateur connecté
    $user = $this->getUser();

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non connecté.');
    }

    // Récupérer le panier de l'utilisateur connecté
    $cart = $cartRepository->findOneBy(['user' => $user]);

    if (!$cart) {
        throw $this->createNotFoundException('Aucun panier trouvé pour cet utilisateur.');
    }

    return $this->render('panier/view.html.twig', [
        'panier' => $cart,
    ]);
}
    public function removeCart(int $cartId, EntityManagerInterface $entityManager): Response
    {
        $cart = $entityManager->getRepository(Cart::class)->find($cartId);

        if (!$cart) {
            return new Response('Panier non trouvé', 404);
        }

        $entityManager->remove($cart);
        $entityManager->flush();

        return new Response('Panier supprimé avec succès');
    }
    #[Route('/panier/{id}', name: 'panier')]
    public function index1(
        int $id, 
        EntityManagerInterface $em,
        Request $request, 
        UserRepository $userRepository,
        CartRepository $cartRepository
    ): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }
    
        // Retrieve the product repository
        $productRepository = $em->getRepository(Product::class);
    
        // Retrieve the product based on the ID passed in the URL
        $product = $productRepository->find($id);
    
        if (!$product) {
            throw $this->createNotFoundException('The requested product does not exist.');
        }
    
        // Retrieve the user's existing cart or create a new one
        $panier = $cartRepository->findOneBy(['user' => $user->getId()]);
    
        if (!$panier) {
            // If the cart doesn't exist, create a new one
            $panier = new Cart();
            $panier->setUser($user);
            $em->persist($panier);
        }
    
        // Check if the product is already in the cart
        $productAlreadyInCart = false;
        foreach ($panier->getProducts() as $cartProduct) {
            if ($cartProduct->getId() === $product->getId()) {
                $productAlreadyInCart = true;
                break;
            }
        }
    
        // Add the product to the cart only if it is not already there
        if (!$productAlreadyInCart) {
            $panier->addProduct($product);
            
            // Update the total price only if a new product has been added
            $totalPrice = 0;
            foreach ($panier->getProducts() as $prod) {
                $totalPrice += $prod->getPrice();
            }
            
            // Update the totalPrice in the cart
            $panier->setTotalPrice($totalPrice);
            // Success message
            $this->addFlash('success', 'The product has been added to the cart.');
            $panier->setTotalPrice($totalPrice);
        } else {
            // Informational message if the product is already in the cart
            $this->addFlash('info', 'This product is already in your cart.');
        }
        
        $em->flush();
    
        return $this->redirectToRoute('panier_show');
    }
    
    
}