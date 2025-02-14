<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry; 
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
   

   

    #[Route('/user', name: 'user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    
    #[Route('/backUser', name: 'back_user')]
    public function backUser(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll(); // Fetch all users

        return $this->render('user/backUser.html.twig', [
            'users' => $users,
            'controller_name' => 'UserController',
        ]);
       
    }

    #[Route('/adduser', name: "add_user")]
    public function addUser(ManagerRegistry $manager, Request $req,UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $manager->getManager();
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]); 
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $confirmPassword = $form->get('confirmPassword')->getData();
    
            if ($confirmPassword !== $user->getPassword()) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->render("user/index.html.twig", [
                    'form' => $form->createView(),
                ]);
            }

            // Hashing the password
        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);
       // $user->setRoles(['ROLE_ADMIN']); 

      // Get selected role from the form
      $selectedRole = $form->get('roles')->getData();
        
   
      if ($selectedRole === 'ROLE_USER') {
          $user->setRoles(['ROLE_USER']);
      } elseif ($selectedRole === 'ROLE_ARTISTE') {
          $user->setRoles(['ROLE_ARTISTE']);
      } else {
          $user->setRoles(['ROLE_USER']); 
      }
    
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('login');
        }
    
        return $this->render("user/index.html.twig", [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/profil', name: 'profil')]
    #[IsGranted('ROLE_USER')]
    public function profil(): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        return $this->render('user/profil.html.twig', [
            'user' => $user,
        ]);
    }

  /*   #[Route('/profil', name: "profil")]
    #[IsGranted('ROLE_USER')]
    public function afficherProfil(): Response
    {
        return $this->render("user/profil.html.twig");
    }
     */
    #[Route('/editprofil/{id}', name: "edit_profil")]
    public function updateUser(UserRepository $repo, ManagerRegistry $manager, Request $req, int $id): Response
    {
        $em = $manager->getManager();
        $user = $repo->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
    
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]); // Pass `true` to exclude password fields
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();
    
            return $this->redirectToRoute("profil");
        }
    
        return $this->render("user/editProfil.html.twig", [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }






}
