<?php
namespace App\Controller;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;



class SecurityController extends AbstractController
{
    #[Route('/loginn', name: 'login')]
    public function loginn(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
       // dump($request->request->all()); // Debugging: Check what data is submitted
        //dump($authenticationUtils->getLastUsername()); // Check last username value

        $form = $this->createForm(LoginType::class);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            return $this->redirectToRoute('app_home'); 
        }

         // Get the login error (if there is any)
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername()?? '';

        return $this->render('user/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername() ?? '';

        if ($this->getUser()) {
            return $this->redirectToRoute('app_redirect_by_role');
        }
    
        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/redirect-by-role', name: 'app_redirect_by_role')]
    public function redirectByRole(): RedirectResponse
    {
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();

            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->redirectToRoute('dash_user');
            } elseif (in_array('ROLE_USER', $roles)) {
                return $this->redirectToRoute('app_home');
            } 
             elseif (in_array('ROLE_ARTISTE', $roles)) {
                return $this->redirectToRoute('profile');
            } 

        }
        return $this->redirectToRoute('app_login');


        // Si aucun rôle trouvé, on redirige vers la page d'accueil
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}
