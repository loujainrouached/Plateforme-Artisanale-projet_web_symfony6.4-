<?php

namespace App\Controller;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User; 
use App\Entity\Cart; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;


final class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(): Response
    {
        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }
    #[Route('/backProduit', name: 'app_produitback')]
    public function backProduit(): Response
    {
        return $this->render('produit/backProduit.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }
  /*   #[Route('/product', name: 'app_product')]
public function showProducts(ProductRepository $pr): Response
{
    $products = $pr->findBy(['status' => 'dispo']); // Fetch only available products
    $categories = $pr->findAllCategories(); 

    return $this->render('produit/index.html.twig', [
        'categories' => $categories,
        'products' => $products, 
    ]);
} */

    #[Route('/listProduct/update/{id}', name: 'update_productback')]
    public function updateProduct_back(
        int $id, 
        ProductRepository $repo, 
        ManagerRegistry $doctrine, 
        Request $request, 
        SluggerInterface $slugger
    ): Response {
        $product = $repo->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Product not found.');
        }
        
        // Créez le formulaire pour mettre à jour un produit
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $product->setStock('1');
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                if ($product->getImage()) {
                    $oldImagePath = $this->getParameter('product_images_directory') . '/' . basename($product->getImage());
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            
                // Générer un nouveau nom de fichier sécurisé
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
            
                try {
                    // Déplacer le fichier téléchargé
                    $imageFile->move(
                        $this->getParameter('product_images_directory'),
                        $newFilename
                    );
                    // Mettre à jour le chemin de l'image dans l'entité
                    $product->setImage('/images/products/'.$newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Could not upload the image.');
                    return $this->redirectToRoute('listProduct');
                }
            }
    
            // Mise à jour et sauvegarde du produit
            $entityManager = $doctrine->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
    
            $this->addFlash('success', 'Product updated successfully.');
            return $this->redirectToRoute('listProduct');
        } 
    
        return $this->render('produit/updateproduct_Back.html.twig', [
            'formProduit' => $form->createView(),
            'product' => $product
        ]);
    }
    

   /*  #[Route('/listProduct', name: 'listProduct')]
public function showProductBack(ProductRepository $pr,ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
{
    $user= $this->getUser();
    $products = $pr->findAll();
    $product = new Product();
    $form = $this->createForm(ProductType::class, $product);
    $form->handleRequest($request);
     if ($form->isSubmitted() && $form->isValid()) {
        
        $product->setStock('1');
        $user = $doctrine->getRepository(User::class)->find($user->getId());
        if ($user) {
            $product->setUser($user);  
        $imageUrl = $form->get('image')->getData();
        $imageFile = $form->get('image')->getData();
    if ($imageFile) {
$originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
$safeFilename = $slugger->slug($originalFilename);
$newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
try {
    $imageFile->move(
        $this->getParameter('product_images_directory'), 
        $newFilename
    );
} catch (FileException $e) {
    $this->addFlash('error', 'Error uploading image.');
    return $this->redirectToRoute('add_product');
}
$product->setImage('/images/products/'.$newFilename);
}    
            $em = $doctrine->getManager();
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'product added successfully.');
            return $this->redirectToRoute('listProduct'); 

        }
    }
    
    return $this->render('produit/backProduit.html.twig', [
        'products' => $products,
        "formProduit" => $form->createView(),
    ]);
} */



#[Route('/listProduct', name: 'listProduct')]
public function showProductBack(
    ProductRepository $pr, 
    ManagerRegistry $doctrine, 
    Request $request, 
    SluggerInterface $slugger, 
    MailerInterface $mailer
): Response {
    $entityManager = $doctrine->getManager();
      // Get the current user or fallback to a default user
      $user = $this->getUser();
      if (!$user) {
          // Use the ManagerRegistry to get the repository instead of $em
          $user = $entityManager->getRepository(User::class)->find($user->getId()); // Default user with ID 1
      }
  
      if (!$user) {
          throw $this->createNotFoundException('Default user with ID 1 not found.');
      }
    // Handle AJAX requests for product approval and rejection
    if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
        $productId = $request->request->get('id');
        $action = $request->request->get('action');
        
        $product = $pr->find($productId);
      
    

        if ($product) {
            try {
                if ($action === 'approve') {
                    $product->setStatus('dispo');
                    $message = 'Product has been approved successfully.';
                    $emailStatus = 'approved';
                } elseif ($action === 'reject') {
                    $product->setStatus('Refuse');
                    $message = 'Product has been rejected.';
                    $emailStatus = 'rejected';
                } else {
                    return new JsonResponse([
                        'success' => false, 
                        'message' => 'Invalid action'
                    ], 400);
                }

                // Flush the changes to the database
                $entityManager->flush();

            
                    try {
                        $email = (new Email())
                        ->from(new Address('skanderselmi19@gmail.com'))
                        ->to((string) $user->getEmail())

                            //->cc('cc@example.com')
                            //->bcc('bcc@example.com')
                            //->replyTo('fabien@example.com')
                            //->priority(Email::PRIORITY_HIGH)
                            ->subject('Product Status Update')
                            ->html(sprintf(
                                '<div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f9f9f9; border-radius: 10px;">
                                    <h2 style="color: #333;">🎉 Dear Seller,</h2>
                                    
                                    <p style="font-size: 16px; color: #555;">
                                        Your product <strong>"%s"</strong> has been <strong style="color: %s;">%s</strong>.
                                    </p>
                                    
                                    <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">
                                        <h3 style="color: #007BFF;">🛍️ Product Details</h3>
                                        <ul style="list-style: none; padding: 0;">
                                            <li>📌 <strong>Name:</strong> %s</li>
                                            <li>📂 <strong>Category:</strong> %s</li>
                                            <li>💰 <strong>Price:</strong> $%s</li>
                                        </ul>
                                    </div>
                            
                                    <div style="margin-top: 20px;">
                                        %s
                                    </div>
                                    
                                    <p style="margin-top: 20px; font-size: 14px; color: #777;">
                                        Thank you for your submission. 🚀<br>
                                        <strong>Need help?</strong> Contact our support team. 📧
                                    </p>
                                    
                                    <img src="%s" alt="Status GIF" style="width: 100%%; max-width: 400px; display: block; margin: auto;">
                                </div>',
                                
                                $product->getName(),
                                $emailStatus === 'approved' ? 'green' : 'red',
                                strtoupper($emailStatus),
                                $product->getName(),
                                $product->getCategory(),
                                number_format($product->getPrice(), 2),
                            
                                $emailStatus === 'approved' 
                                    ? '<p style="color: green; font-weight: bold; font-size: 16px;">✅ Congratulations! Your product is now live and available for sale.</p>'
                                    : '<p style="color: red; font-weight: bold; font-size: 16px;">❌ Unfortunately, your product has been rejected.</p>
                                       <p>📝 <strong>Feedback:</strong></p>
                                       <ul>
                                           <li>🔍 Please review the product details carefully.</li>
                                           <li>🛠️ Make necessary improvements.</li>
                                           <li>📩 Resubmit for another evaluation.</li>
                                       </ul>',
                                
                                $emailStatus === 'approved' 
                                    ? 'https://media.giphy.com/media/l41lI4bYmcsPJX9Go/giphy.gif' 
                                    : 'https://media.giphy.com/media/d2lcHJTG5Tscg/giphy.gif'
                            ))
                            ;
                 
                     $mailer->send($email); // Send the email
                     return new Response('✅ Test email sent successfully!');
                 } catch (TransportExceptionInterface $e) {
                     return new Response('❌ Error sending email: ' . $e->getMessage());
                 }
              

                return new JsonResponse([
                    'success' => true, 
                    'message' => $message
                ]);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false, 
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
        }

        return new JsonResponse([
            'success' => false, 
            'message' => 'Product not found'
        ], 404);
    }

    // Rest of the existing method remains the same
    $products = $pr->findAll();

    $pendingProducts = $pr->find_En_Attente_Products(); // Récupère les produits en attente
    $pendingCount = $pr->countPendingProducts();

    $product = new Product();
    $form = $this->createForm(ProductType::class, $product);
    $form->handleRequest($request);

    return $this->render('produit/backProduit.html.twig', [
        'products' => $products,
        'pendingProducts' => $pendingProducts, 
        'pendingCount' => $pendingCount,
        'formProduit' => $form->createView(),
    ]);
}
    #[Route('/product/profile', name: 'user_products')]
    public function userProducts(ProductRepository $productRepository): Response
    {
        $user = $this->getUser() ;
        
        $products = $productRepository->findProductsByUser($user->getId());
    
        return $this->render('produit/user_products.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/product/add', name: 'add_product1')]
    public function addProduct(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {

      

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $user = $this->getUser();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setStock('1');
            $product->setStatus('en attente');
            

            $user = $doctrine->getRepository(User::class)->find($user->getId());

            if ($user) {
                
                $product->setUser($user);  
             
            $imageUrl = $form->get('image')->getData();

$imageFile = $form->get('image')->getData();

if ($imageFile) {
  
    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $slugger->slug($originalFilename);
    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

 
    try {
        $imageFile->move(
            $this->getParameter('product_images_directory'), 
            $newFilename
        );
    } catch (FileException $e) {
     
        $this->addFlash('error', 'Error uploading image.');
        return $this->redirectToRoute('add_product');
    }

    
    $product->setImage('/images/products/'.$newFilename);
}

               
                $em = $doctrine->getManager();
                $em->persist($product);
                $em->flush();

          
                return $this->redirectToRoute('app_product'); 
            } else {
           
                $this->addFlash('error', 'User not found.');
            }
        }

        return $this->render('produit/addproduct.html.twig', [
            'formProduit' => $form->createView(),
        ]);
    }


    #[Route('/product/update/{id}', name: 'update_product')]
public function updateProduct(int $id, ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
{
   
    $product = $doctrine->getRepository(Product::class)->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Product not found.');
    }

 
    $form = $this->createForm(ProductType::class, $product);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $product->setStock('1');
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('product_images_directory'),
                    $newFilename
                );
                $product->setImage($newFilename);  // Mettre à jour le nom de l'image dans l'entité
            } catch (FileException $e) {
             
                $this->addFlash('error', 'Could not upload the image.');
            }
        }

      
        $entityManager = $doctrine->getManager();
        $entityManager->flush();

      
        $this->addFlash('success', 'Product updated successfully.');
        return $this->redirectToRoute('app_product');
    }

    return $this->render('produit/updateproduct.html.twig', [
        'formProduit' => $form->createView(),
    ]);
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

    #[Route('/produit/delete/{id}', name: 'delete_product')]
    public function deleteProduct($id, ManagerRegistry $doctrine): Response
    {
        $product = $doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('app_product');
        }

        $em = $doctrine->getManager();
        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'Product deleted successfully.');
        return $this->redirectToRoute('user_products');
    }
    #[Route('/listProduct/delete/{id}', name: 'delete_productback')]
    public function deleteProductback($id, ManagerRegistry $doctrine): Response
    {
        $product = $doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('listProduct');
        }

        $em = $doctrine->getManager();
        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'Product deleted successfully.');
        return $this->redirectToRoute('listProduct');
    }

    #[Route('/produitCart/delete/{id}', name: 'delete_productFromCart')]
public function deleteProductFromCart($id, ManagerRegistry $doctrine): Response
{
    // Récupérer l'utilisateur avec ID = 1
    $user = $this->getUser();
    $user = $doctrine->getRepository(User::class)->find($user->getId());

    if (!$user) {
        $this->addFlash('error', 'User not found.');
        return $this->redirectToRoute('panier_show');
    }

    // Récupérer le panier de l'utilisateur
    $cart = $doctrine->getRepository(Cart::class)->findOneBy(['user' => $user]);

    if (!$cart) {
        $this->addFlash('error', 'Your cart is empty.');
        return $this->redirectToRoute('panier_show');
    }

    // Récupérer le produit à supprimer
    $product = $doctrine->getRepository(Product::class)->find($id);

    if (!$product) {
        $this->addFlash('error', 'Product not found.');
        return $this->redirectToRoute('panier_show');
    }

    // Vérifier si le produit est dans le panier
    if ($cart->getProducts()->contains($product)) {
        // Retirer le produit du panier
        $cart->removeProduct($product); // Méthode removeProduct à définir dans la classe Cart

        // Mettre à jour le prix total du panier
        $totalPrice = 0;
        foreach ($cart->getProducts() as $prod) {
            $totalPrice += $prod->getPrice();
        }

        // Mettre à jour le totalPrice dans le panier
        $cart->setTotalPrice($totalPrice);

        // Sauvegarder les changements
        $em = $doctrine->getManager();
        $em->flush();

        $this->addFlash('success', 'Product removed from cart.');
    } else {
        $this->addFlash('info', 'Product is not in your cart.');
    }

    return $this->redirectToRoute('panier_show');
}

#[Route('/product', name: 'app_product')]
public function showProducts(ProductRepository $pr): Response
{
    $products = $pr->findAvailableProducts(); // Récupère uniquement les produits disponibles
    $categories = $pr->findAllCategories();
    
    // Calcul des prix min et max directement dans la méthode
    $priceRange = (function(array $products): array {
        
        
        $prices = [];
        foreach ($products as $product) {
            $prices[] = $product->getPrice();
        }
        
        return [
            'min' => floor(min($prices)),
            'max' => ceil(max($prices))
        ];
    })($products);
    
    return $this->render('produit/index.html.twig', [
        'categories' => $categories,
        'products' => $products,
        'priceRange' => $priceRange,
    ]);
}
    #[Route('/product/search', name: 'product_search', methods: ['GET'])]
public function search(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    try {
        $query = $request->query->get('q');
        
        // S'assurer que la requête n'est pas vide
        if (empty($query)) {
            return $this->json(['products' => []]);
        }
        
        // Utiliser QueryBuilder pour la recherche
        $qb = $entityManager->createQueryBuilder();
        
        $qb->select('p')
        ->from(Product::class, 'p')
        ->where('p.name = :query')
        ->orWhere('p.category = :query')
        ->orWhere('p.description = :query')
        ->setParameter('query', $query)
        ->setMaxResults(12);
        $products = $qb->getQuery()->getResult();
        
        // Transformer les produits en format JSON
        $productsArray = [];
        foreach ($products as $product) {
            $productsArray[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'category' => $product->getCategory(),
                // Gestion de l'image de manière plus sûre
                'image' => $product->getImage() ?: '/images/default-product.jpg',
                
                // Ajoutez d'autres champs si nécessaire
            ];
        }
        
        return $this->json(['products' => $productsArray]);
    } catch (\Exception $e) {
        // Log l'erreur pour le débogage
        // $this->logger->error('Search error: ' . $e->getMessage());
        
        // Retourner une réponse d'erreur plus détaillée en développement
        return $this->json([
            'error' => 'Une erreur est survenue lors de la recherche',
            'message' => $e->getMessage(), // À supprimer en production
            'products' => []
        ], 200); // Code 200 pour que le frontend puisse afficher le message
    }
}

#[Route('/product/approve/{id}', name: 'approve_product')]
public function approveProduct(int $id, ManagerRegistry $doctrine): JsonResponse
{
    $entityManager = $doctrine->getManager();
    $product = $entityManager->getRepository(Product::class)->find($id);

    if (!$product) {
        return new JsonResponse(['success' => false, 'message' => 'Produit introuvable.'], 404);
    }

    $product->setStatus('approved');
    $entityManager->flush();

    return new JsonResponse([
        'success' => true,
        'message' => 'Le produit a été approuvé avec succès.',
        'newStatus' => 'approved'
    ]);
}
#[Route('/product/reject/{id}', name: 'reject_product')]
public function rejectProduct(int $id, ManagerRegistry $doctrine): JsonResponse
{
$entityManager = $doctrine->getManager();
$product = $entityManager->getRepository(Product::class)->find($id);

if (!$product) {
    return new JsonResponse(['success' => false, 'message' => 'Produit introuvable.'], 404);
}

$product->setStatus('rejected');
$entityManager->flush();

return new JsonResponse([
    'success' => true,
    'message' => 'Le produit a été rejeté avec succès.',
    'newStatus' => 'rejected'
]);
}
    
    
}
 