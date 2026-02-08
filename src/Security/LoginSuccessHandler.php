<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token
    ): RedirectResponse {
        $roles = $token->getUser()->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse(
                $this->router->generate('backoffice_home')
            );
        }

        // Coach ou Player
        return new RedirectResponse(
            $this->router->generate('app_home')
        );
    }
}
