<?php
namespace App\Controller;

use Symfony\Component\Security\Core\Security;
use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class SecurityController extends AbstractController
{
   
// Add this method to the controller
/* private function getTokenFromSession(): ?string
{
    return $this->get('session')->get('reset_token');
}
 */
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils,Security $security,Request $request): Response
    {
        $resetToken = $request->getSession()->get('reset_token');

       
         /*    $email = $request->request->get('email');
            $password = $request->request->get('password');
  
             // Check if the user is already authenticated
             $recaptchaResponse = $request->request->get('g-recaptcha-response'); */

    if ($this->getUser()) {
        $user = $this->getUser();
        
        // Check if user is banned
        if ($user->getIsBanned()) {
            // Set the 'banned' session variable
            $this->get('session')->set('banned', true);
            // Redirect to login to show the banned message
            return $this->redirectToRoute('login');
        }

        /*    // Vérification reCAPTCHA
           if (!$recaptchaResponse) {
            $this->addFlash('error', 'Veuillez valider le reCAPTCHA.');
            return $this->redirectToRoute('login');
        }

        $client = HttpClient::create();
        $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $_ENV['GOOGLE_RECAPTCHA_SECRET'], // Clé secrète
                'response' => $recaptchaResponse
            ]
        ]);

        $data = $response->toArray();

        if (!$data['success']) {
            $this->addFlash('error', 'Échec de la validation reCAPTCHA.');
            return $this->redirectToRoute('login');
        } */
        
        // If user is authenticated and not banned, proceed to redirect based on role
        return $this->redirectToRoute('app_redirect_by_role');
    }
    

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername() ?? '';
    
          
        
             // Check if the error message contains "banned" and add the appropriate full message
       /*       if ($error && strpos($error->getMessage(), 'banned') !== false) {
                $fullErrorMessage = 'Your account has been banned. Please contact support.';
            } else {
                $fullErrorMessage = $error ? $error->getMessage() : '';
            } */
        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'resetToken' => $resetToken,
            /* 'recaptcha_site_key' => $_ENV['GOOGLE_RECAPTCHA_SITE_KEY'] */
        ]);
    }

    #[Route(path: '/redirect-by-role', name: 'app_redirect_by_role')]
    public function redirectByRole(): RedirectResponse
    {
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();

            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->redirectToRoute('back_user');
            } elseif (in_array('ROLE_CLIENT', $roles) || in_array('ROLE_ARTISTE', $roles)) {
                return $this->redirectToRoute('app_home');
            }
        }
        return $this->redirectToRoute('login');
    }

  /*   #[Route('/check-login', name: 'check_login')]
public function checkLogin(Security $security): Response
{
    $user = $security->getUser();

    if ($user && $user->getIsBanned()) {
        $this->addFlash('error', 'Your account has been banned. Please contact support.');
        return $this->redirectToRoute('logout');
    }

    return $this->redirectToRoute('app_home');
} */





    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }



 /*    #[Route('/loginn', name: 'login')]
    public function loginn(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
      

        $form = $this->createForm(LoginType::class);

        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            return $this->redirectToRoute('app_home'); 
        }

        
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername()?? '';

        return $this->render('user/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    } */


}
