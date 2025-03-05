<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry; 
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\UserType;
use App\Form\AddBackType;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use App\Form\ReservationType;
use App\Form\OrderType;
use App\Repository\ReservationRepository;  
use App\Repository\OrderRepository;  

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;





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
   // #[IsGranted('ROLE_ADMIN')]
    public function backUser(UserRepository $userRepository, Security $security): Response
    {
        $user = $this->getUser();

        /* if (!$security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('profil'); 
        } */
        $users = $userRepository->findAll();  

        return $this->render('user/backUser.html.twig', [
            'users' => $users,
            'controller_name' => 'UserController',
            'user'=>$user,
        ]);
       
    }


    #[Route('/addBack', name: 'add_back')]
    public function addback(ManagerRegistry $manager, Request $req,UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $manager->getManager();
        $user = new User();
        $form = $this->createForm(AddBackType::class,$user, ['is_edit' => false]); 
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $confirmPassword = $form->get('confirmPassword')->getData();
    
            if ($confirmPassword !== $user->getPassword()) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
/*           $form->get('confirmPassword')->addError(new FormError('Les mots de passe ne correspondent pas.')); */              
             return $this->render('user/addBack.html.twig', [
            //'users' => $users,
            'form' => $form->createView(),
            'controller_name' => 'UserController',
        ]);
    }
  /*   $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
    $user->setPassword($hashedPassword); */



    $newPassword = $form->get('password')->getData();
    $confirmPassword = $form->get('confirmPassword')->getData();

    // Only update password if a new one is entered
    if (!empty($newPassword)) {
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        
        $hashedPassworddd = $passwordHasher->hashPassword($user, $confirmPassword);
        
        if ($newPassword !== $confirmPassword) {
            $form->get('confirmPassword')->addError(new FormError('Les mots de passe ne correspondent pas.'));
            return $this->render("user/addBack.html.twig", [
                'form' => $form->createView(),
            ]);
        }
        $user->setPassword($hashedPassword);
    }

    $photo = $form->get('photo')->getData();
    if ($photo) {
     
        $newFilename = uniqid() . '.' . $photo->guessExtension();
        try {
            $photo->move($this->getParameter('profile_pictures_directory'), $newFilename);
            $user->setPhoto('/uploads/profile_pictures/' . $newFilename);
        } catch (FileException $e) {
            $this->addFlash('error', 'Failed to upload file.');
        }

    }


     
    /*   $photoFile = $form->get('photoFile')->getData();
      if ($photoFile) {
          $newFilename = uniqid() . '.' . $photoFile->guessExtension();
          try {
              $photoFile->move($this->getParameter('profile_pictures_directory'), $newFilename);
              $user->setPhoto($newFilename);
          } catch (FileException $e) {
              $this->addFlash('error', 'Failed to upload file.');
          }
      } */


        $selectedRole = $form->get('roles')->getData();

        if ($selectedRole === 'ROLE_CLIENT') {
            $user->setRoles(['ROLE_CLIENT']);
        } elseif ($selectedRole === 'ROLE_ARTISTE') {
            $user->setRoles(['ROLE_ARTISTE']);
        } elseif ($selectedRole === 'ROLE_ADMIN') {
            $user->setRoles(['ROLE_ADMIN']); 
        } else {
            $user->setRoles(['ROLE_CLIENT']); 
        }

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('back_user');
    }
    return $this->render('user/addBack.html.twig', [
        //'users' => $users,
        'form' => $form->createView(),
        'controller_name' => 'UserController',
    ]);
}

#[Route('/adduser', name: "add_user")]
public function addUser(
    ManagerRegistry $manager,
    Request $req,
    UserPasswordHasherInterface $passwordHasher,
    MailerInterface $mailer,
    VerifyEmailHelperInterface $verifyEmailHelper
): Response {
    $em = $manager->getManager();
    $user = new User();
    $form = $this->createForm(UserType::class, $user, ['is_edit' => false]); 
    $form->handleRequest($req);

    if ($form->isSubmitted() && $form->isValid()) {
        $confirmPassword = $form->get('confirmPassword')->getData();

        if ($confirmPassword !== $user->getPassword()) {
            $form->get('confirmPassword')->addError(new FormError('Les mots de passe ne correspondent pas.'));
            return $this->render("user/index.html.twig", [
                'form' => $form->createView(),
            ]);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);
        $selectedRole = $form->get('roles')->getData();
        $user->setRoles([$selectedRole]);
        $user->setIsVerified(false); // User is not verified initially


        $em->persist($user);
        $em->flush();

        // Generate the verification link
        $signatureComponents = $verifyEmailHelper->generateSignature(
            'verify_email',
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

       
        
        // Send email
        $email = (new TemplatedEmail())
            ->from('skanderselmi19@gmail.com')
            ->to($user->getEmail())
/*             ->subject('Verify Your Email Address')*/   
            ->subject('Welcome, ' . $user->getName() . '! Verify Your Email 🎉')
          // ->html(sprintf('<p>Please verify your email by clicking <a href="%s">here</a>.</p>', $signatureComponents->getSignedUrl()));
           ->htmlTemplate('user/verify_email.html.twig')
           ->context([
               'user' => $user,
               'verification_url' => $signatureComponents->getSignedUrl()
           ]);
        $mailer->send($email);

        return $this->redirectToRoute('login');
    }

    return $this->render("user/index.html.twig", [
        'form' => $form->createView(),
    ]);
}



#[Route('/verify/email', name: 'verify_email')]
public function verifyEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, ManagerRegistry $manager): Response
{
    $em = $manager->getManager();
    $user = $em->getRepository(User::class)->find($request->query->get('id'));

    if (!$user) {
        throw $this->createNotFoundException('User not found');
    }

    try {
        $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
        $user->setIsVerified(true);
        $em->flush();

        return $this->redirectToRoute('login');
    } catch (VerifyEmailExceptionInterface $exception) {
        throw new \Exception('Email verification failed.');
    }
}


    #[Route('/profil', name: 'profil')]
    //#[IsGranted('ROLE_ARTISTE')]
  //  #[IsGranted('ROLE_CLIENT')]
    // #[IsGranted('ROLE_ADMIN')]
    public function profil(ReservationRepository $reservationRepository, OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        
        $orders = $orderRepository->findBy(['user' => $user]);
        
        $reservations = $reservationRepository->findBy(['user' => $user]);
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'reservations' => $reservations,
            'orders'=> $orders,
        ]);
    }

    #[Route('/profilBack', name: 'profil_back')]
    //#[IsGranted('ROLE_ARTISTE')]
  //  #[IsGranted('ROLE_CLIENT')]
    // #[IsGranted('ROLE_ADMIN')]
    public function profilBack(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        return $this->render('user/profilBack.html.twig', [
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
            
            $form = $this->createForm(UserType::class, $user, ['is_edit' => true]); 
            $form->handleRequest($req);
        
            if ($form->isSubmitted() && $form->isValid()) {
                $oldEmail = $user->getEmail(); // Store the old email before update

                $em->persist($user);
                $em->flush();
        
                // If the email was changed, re-authenticate the user
                if ($oldEmail !== $user->getEmail()) {
                    $token = new PostAuthenticationToken($user, 'main', $user->getRoles());
                    $security->getTokenStorage()->setToken($token);
                }
        
                return $this->redirectToRoute("profil");
            }
        
            return $this->render("user/editProfil.html.twig", [
                'form' => $form->createView(),
                'user' => $user,
            ]);
        }



    #[Route('/editBack/{id}', name: "edit_back")]
    public function updateBack(UserRepository $repo, ManagerRegistry $manager, Request $req, int $id,UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $manager->getManager();
        $user = $repo->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
    
        $form = $this->createForm(AddBackType::class, $user, ['is_edit' => true]); 
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {

           
            $photo = $form->get('photo')->getData();
            if ($photo) {
                $newFilename = uniqid() . '.' . $photo->guessExtension();
                try {
                    $photo->move($this->getParameter('profile_pictures_directory'), $newFilename);
                    $user->setPhoto('/uploads/profile_pictures/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload file.');
                }
            }
         
            // Get the new password from the form
            $newPassword = $form->get('password')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            // Only update password if a new one is entered
            if (!empty($newPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                
                $hashedPassworddd = $passwordHasher->hashPassword($user, $confirmPassword);
                
                if ($newPassword !== $confirmPassword) {
                    $form->get('confirmPassword')->addError(new FormError('Les mots de passe ne correspondent pas.'));
                    return $this->render("user/updateBack.html.twig", [
                        'form' => $form->createView(),
                    ]);
                }
                $user->setPassword($hashedPassword);
            }

           

            $selectedRole = $form->get('roles')->getData();
        
   
            if ($selectedRole === 'ROLE_CLIENT') {
                $user->setRoles(['ROLE_CLIENT']);
            } elseif ($selectedRole === 'ROLE_ARTISTE') {
                $user->setRoles(['ROLE_ARTISTE']);
            } elseif ($selectedRole === 'ROLE_ADMIN') {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_CLIENT   ']); 
            }
        
   
            $em->persist($user);
            $em->flush();
    
            return $this->redirectToRoute("back_user");
        }

        
   

    
        return $this->render("user/updateBack.html.twig", [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }


  


    #[Route('/upload-profile-picture', name: 'upload_profile_picture')]
    public function uploadProfilePicture(Request $request, EntityManagerInterface $em, Security $security, SluggerInterface $slugger): Response
    {
        $user = $security->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }
    
        $file = $request->files->get('profile_picture');
    
        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
    
            $file->move($this->getParameter('profile_pictures_directory'), $newFilename);
    
            $user->setPhoto('/uploads/profile_pictures/' . $newFilename);
            $em->persist($user);
            $em->flush();
        }
    
        return $this->redirectToRoute('profil');
    }
    
  

    #[Route('/user/delete/{id}', name: 'delete_account', methods: ['POST'])]
    public function deleteAccount(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        // Check if the logged-in user is deleting their own account
        if ($this->getUser() !== $user) {
            throw $this->createAccessDeniedException('Unauthorized action.');
        }

        // Get the CSRF token from the request
        $submittedToken = $request->request->get('_token');

        // Validate CSRF token
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete-account-' . $user->getId(), $submittedToken))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // Remove user from the database
        $entityManager->remove($user);
        $entityManager->flush();

        // Logout the user
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        // Redirect to home page
        return $this->redirectToRoute('app_home');
    }


/*     #[IsGranted('ROLE_ADMIN')]
 */    #[Route('/user/delete/{id}', name: 'delete_account', methods: ['POST'])]
    public function deleteAccountBack(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        // Get logged-in user
        $currentUser = $this->getUser();
    
        // Allow user to delete themselves OR allow admins to delete any user
        if ($currentUser !== $user && !in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            throw new AccessDeniedException('Unauthorized action.');
        }
    
        // Get CSRF token from the request
        $submittedToken = $request->request->get('_token');
    
        // Validate CSRF token
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete-account-' . $user->getId(), $submittedToken))) {
            throw new AccessDeniedException('Invalid CSRF token.');
        }
    
        // Remove user from the database
        $entityManager->remove($user);
        $entityManager->flush();
    
        // If the user deleted themselves, log them out and invalidate session
        if ($currentUser === $user) {
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();
    
            return $this->redirectToRoute('app_home'); // Redirect to home page
        }
    
        // If an admin deleted another user, redirect to user management page
        return $this->redirectToRoute('back_user');
    }


/* #[Route('/admin/toggle-ban/{id}', name: 'toggle_ban')]
public function toggleBanUser(int $id, ManagerRegistry $manager): Response
{
    $em = $manager->getManager();
    $user = $em->getRepository(User::class)->find($id);

    if (!$user) {
        throw $this->createNotFoundException('User not found.');
    }

    // Toggle the isBanned status
    $user->setIsBanned(!$user->getIsBanned());
    $em->flush();

    return $this->redirectToRoute('back_user'); // Redirect to the user list page
} */

#[Route('/toggle-ban/{id}', name: 'toggle_ban', methods: ['POST'])]
public function toggleBan($id, EntityManagerInterface $em, Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
{
    $user = $em->getRepository(User::class)->find($id);
    
    if (!$user) {
        throw $this->createNotFoundException('User not found.');
    }

    // Verify CSRF Token
    $token = new CsrfToken('toggle-ban-' . $id, $request->request->get('_token'));
    if (!$csrfTokenManager->isTokenValid($token)) {
        throw $this->createAccessDeniedException('Invalid CSRF token.');
    }

    // Toggle Ban Status
    $user->setIsBanned(!$user->getIsBanned());
    $em->persist($user);
    $em->flush();

    $this->addFlash('success', $user->getIsBanned() ? 'User has been banned.' : 'User has been unbanned.');
    return $this->redirectToRoute('back_user');
}



}
