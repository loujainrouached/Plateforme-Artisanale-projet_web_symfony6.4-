<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\UserRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
// use Anhskohbo\NoCaptcha\NoCaptcha;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private UserRepository $userRepository;
    private RouterInterface $router;

    public function __construct(UserRepository $userRepository, RouterInterface $router)
    {
        $this->userRepository = $userRepository;
        $this->router = $router;
    }

    public function authenticate(Request $request): Passport
    {

        $email = $request->request->get('email', '');
        $user = $this->userRepository->findOneBy(['email' => $email]);


        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Invalid credentials.');
        }
           if (!$user->isVerified()) {
                throw new CustomUserMessageAuthenticationException('Please verify your email before logging in.');
            }
        

        // ❌ Block banned users
        if ($user && $user->getIsBanned()) {
            throw new CustomUserMessageAuthenticationException('Your account has been banned.');
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password')),
            [new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token'))]
        );
    }

  
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        // ❌ Prevent banned users from logging in
        if (method_exists($user, 'getIsBanned') && $user->getIsBanned()) {
            throw new CustomUserMessageAuthenticationException('Your account has been banned.');
        }

        // ✅ Redirect based on user role
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->router->generate('back_user'));
        } elseif (in_array('ROLE_CLIENT', $user->getRoles()) || in_array('ROLE_ARTISTE', $user->getRoles())) {
            return new RedirectResponse($this->router->generate('workshop_detail'));
        }

        return new RedirectResponse($this->router->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('login');
    }
}
