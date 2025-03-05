<?php

namespace App\Controller;
use App\Entity\Order;
use App\Entity\Cart; // Assure-toi d'importer l'entité Cart

use App\Entity\User;
use App\Form\OrderType;
use App\Repository\CartRepository;
use App\Repository\ReservationRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\ProductRepository;

final class OrderController extends AbstractController
{
  
#[Route('/order/cart/{cartId}', name: 'order_cart')]
public function showCart(
    int $cartId,
    CartRepository $cartRepository,
    OrderRepository $orderRepository,
    EntityManagerInterface $em
): Response {
    $cart = $cartRepository->find($cartId);

    if (!$cart) {
        throw $this->createNotFoundException('Panier non trouvé.');
    }

    $existingOrder = $orderRepository->findOneBy(['Cart' => $cart]);
    
    if ($existingOrder) {
        $this->addFlash('info', 'Please fill your cart while waiting for your current order to be processed');
        // TODO: Remplacer 'cart_show' par le nom correct de votre route d'affichage du panier
        return $this->redirectToRoute('panier_show', ['id' => $cartId]); // ou le nom que vous utilisez
    }

    $order = new Order();
    $order->setCart($cart);
    $order->setUser($cart->getUser());
    $order->setDateOrder(new \DateTime());
    $order->setDeliveryAdress('');
    $order->setPhoneNumber(0);
    
    $em->persist($order);
    $em->flush();
    
    return $this->redirectToRoute('confirm_order', ['id' => $order->getId()]);
}

#[Route('/product/{id}', name: 'product_details')]
public function detail(ProductRepository $productRepository, int $id): Response
{
    $product = $productRepository->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Product not found');
    }

    // Récupérer les produits similaires (exemple basé sur la même catégorie)
    $relatedProducts = $productRepository->findBy(
        ['category' => $product->getCategory()], // Même catégorie
        ['id' => 'DESC'], // Trier par ID descendant
        3 // Limite à 3 produits similaires
    );

    return $this->render('produit/singleProduct.html.twig', [
        'product' => $product,
        'related_products' => $relatedProducts, // Ajouter les produits similaires
    ]);
}

#[Route('/order/historique', name: 'user_orders')]
public function userOrders(OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
{
    // Hardcoded userId = 1 as requested
    $user =$this->getUser();
    
    // Get the user with ID = 1 from the database

    $userRepository = $entityManager->getRepository(User::class);
  
    $user = $userRepository->find($user->getId());
    
    if (!$user) {
        throw $this->createNotFoundException('User with ID 1 not found.');
    }
    
    $orders = $orderRepository->findBy(
        ['user' => $user]
       
    );
 //   $orderHistory = json_decode($order->getOrderHistory(), true); // true for associative array

    return $this->render('order/user_order.html.twig', [
        'orders' => $orders,
    'user' => $user,
 
    ]);
}  
/////////////////////////////////////////////////////////////////////////
#[Route('/order/confirm/{id}', name: 'confirm_order')]
public function confirmOrder(
    Request $request,
    Order $order,
    EntityManagerInterface $em
): Response {
    // Créer le formulaire basé sur OrderType
    $form = $this->createForm(OrderType::class, $order);
    $form->handleRequest($request);

    // Récupérer le panier associé à la commande
    $cart = $order->getCart();

    if (!$cart) {
        throw $this->createNotFoundException('No cart found for this order.');
    }

    // Récupérer les produits du panier
    $products = $cart->getProducts();

    // Construire l'historique des achats sous forme de texte (ici JSON)
    $orderHistory = [];
    foreach ($products as $product) {
        $orderHistory[] = [
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'quantity' => 1, // Modifier si tu gères la quantité
        ];
   

    }
    
// Ajouter le prix total à l'historique (après les produits)
$orderHistory[] = [
    'totalPrice' => $cart->getTotalPrice(), // Utiliser le total du panier
];
    $order->setOrderHistory(json_encode($orderHistory)); // Convertir en JSON et stocker dans orderHistory

    if ($form->isSubmitted() && $form->isValid()) {
        
        $product->setStatus('non_dispo');  
        
        // Optionnel: Vider le panier après la confirmation de la commande
         $cart->removeAllProducts(); // Méthode fictive pour vider le panier
         
         $em->flush();
        // Ajouter un message de succès avec la liste des produits
        $message = 'Your order has been confirmed!';
        $this->addFlash('success', $message);

        // Désactiver le formulaire après confirmation
        $form = $this->createForm(OrderType::class, $order, [
            'disabled' => true
        ]);
    }

    // Passer les produits et le formulaire au template
    return $this->render('order/cartToOrder.html.twig', [
        'form' => $form->createView(),
        'order' => $order,
        'orderHistory' => $orderHistory,  // Passer la liste des produits à la vue
    ]);
}


#[Route('/admin/orders', name: 'admin_all_orders')]
public function allOrders(OrderRepository $orderRepository): Response
{
    // Récupérer toutes les commandes avec les relations user
    $orders = $orderRepository->findAll();

    return $this->render('order/all_orders.html.twig', [
        'orders' => $orders,
    ]);
}





#[Route("/admin/order/delete-user-order", name: "admin_delete_user_order", methods: ["GET"])]
public function deleteUserOrder(
    EntityManagerInterface $em,
    Request $request,
    OrderRepository $orderRepository,
    ProductRepository $productRepository
): Response {
    // CSRF token verification
    $user =$this->getUser();
    $token = $request->query->get('token');
    if (!$this->isCsrfTokenValid('delete-user-order', $token)) {
        $this->addFlash('error', 'Invalid CSRF token');
        return $this->redirectToRoute('profil');
    }
    
    // Retrieve order ID from the request
    $orderId = $request->query->get('orderId');
    if (!$orderId) {
        $this->addFlash('error', 'Missing order ID');
        return $this->redirectToRoute('profil');
    }
    
    // Retrieve the order for a specific user (userId = 1)
    $order = $orderRepository->findOneBy([
        'id' => $orderId,
        'user' =>$user->getId()// Filter by userId = 1
    ]);
    
    if (!$order) {
        $this->addFlash('error', 'Order not found for this user');
        return $this->redirectToRoute('profil');
    }
     // Check if the order is older than 2 days
     $dateNow = new \DateTime();
     $dateOrder = $order->getDateOrder();
     $interval = $dateNow->diff($dateOrder);
     
     if ($interval->days > 2) {
         $this->addFlash('error', 'You can only delete orders that are older than 2 days');
         return $this->redirectToRoute('profil');
     }
    try {
        // Retrieve the associated cart
        $cart = $order->getCart();
        
        // Retrieve and decode order history
        $orderHistory = json_decode($order->getOrderHistory(), true);
        
        // Update product status to 'available'
        if ($orderHistory && is_array($orderHistory)) {
            foreach ($orderHistory as $item) {
                // Ignore total price entry
                if (isset($item['name'])) {
                    // Find product by name
                    $product = $productRepository->findOneBy(['name' => $item['name']]);
                    if ($product) {
                        $product->setStatus('dispo');
                        $em->persist($product);
                    }
                }
            }
        }
        
        // Delete the order
        $em->remove($order);
        
        // Delete the cart if it's empty
        if ($cart && count($cart->getProducts()) === 0) {
            $em->remove($cart);
        }
        
        $em->flush();
        
        $this->addFlash('success', 'Order #' . $orderId . ' for user ID 1 has been deleted, and the products are now available');
    } catch (\Exception $e) {
        $this->addFlash('error', 'Error while deleting the order: ' . $e->getMessage());
    }
    
    return $this->redirectToRoute('profil');

}

#[Route('/user/order/update/{id}', name: 'user_update_order', methods: ['GET', 'POST'])]
public function updateUserOrder(
    Order $order,
    Request $request,
    EntityManagerInterface $em
): Response {
    // Vérifier que l'utilisateur actuel est bien le propriétaire de la commande
    $user = $this->getUser();
        if (!$user) {
            // Use $entityManager instead of $em
            $user = $em->getRepository(User::class)->find($user->getId());
        }
    // Create form with validation group
    $form = $this->createForm(OrderType::class, $order);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $em->persist($order);

        $em->flush();
        $this->addFlash('success', 'Delivery information updated successfully.');
        return $this->redirectToRoute('profil');
    }

    return $this->render('order/order_update.html.twig', [
        'order' => $order,
        'user'=>$user,
        'form' => $form->createView()
    ]);
}

}