<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;
    private TokenStorageInterface $tokenStorage;

    public function __construct(RouterInterface $router, TokenStorageInterface $tokenStorage)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token
    ): RedirectResponse {
        $user = $token->getUser();

        if ($user instanceof User && $user->isTotpEnabled() && $user->getTotpSecret()) {
            // Store the token and user info in session, then clear auth
            $session = $request->getSession();
            $session->set('2fa_pending_user_id', $user->getId());
            $session->set('2fa_pending_secret', $user->getTotpSecret());
            $session->set('2fa_pending_token', serialize($token));

            // Determine target URL based on role
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                $session->set('2fa_target_url', $this->router->generate('backoffice_home'));
            } else {
                $session->set('2fa_target_url', $this->router->generate('app_home'));
            }

            // Remove the security token so user is not fully authenticated
            $this->tokenStorage->setToken(null);
            $session->remove('_security_main');

            return new RedirectResponse(
                $this->router->generate('app_login_2fa_verify')
            );
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse(
                $this->router->generate('backoffice_home')
            );
        }

        return new RedirectResponse(
            $this->router->generate('app_home')
        );
    }
}
