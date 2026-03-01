<?php

namespace App\EventListener;

use App\Service\RecaptchaService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

#[AsEventListener(event: 'kernel.request', priority: 10)]
class LoginRecaptchaListener
{
    public function __construct(
        private RecaptchaService $recaptchaService,
        private RouterInterface $router,
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_route') !== 'app_login' || !$request->isMethod('POST')) {
            return;
        }

        $recaptchaResponse = $request->request->get('g-recaptcha-response');

        if (!$this->recaptchaService->verify($recaptchaResponse)) {
            $request->getSession()->set('_security.last_error', new \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException('Veuillez valider le reCAPTCHA.'));
            $request->getSession()->set('_security.last_username', $request->request->get('_username', ''));

            $event->setResponse(new RedirectResponse($this->router->generate('app_login')));
            $event->stopPropagation();
        }
    }
}
