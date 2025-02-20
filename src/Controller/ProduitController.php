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

use Symfony\Component\String\Slugger\SluggerInterface;
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
    #[Route('/product', name: 'app_product')]
public function showProducts(ProductRepository $pr): Response
{
    $products = $pr->findBy(['status' => 'dispo']); // Fetch only available products
    $categories = $pr->findAllCategories(); 

    return $this->render('produit/index.html.twig', [
        'categories' => $categories,
        'products' => $products, 
    ]);
}

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
    

    #[Route('/listProduct', name: 'listProduct')]
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
            $product->setStatus('dispo');
            

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

    
    
}
 