<?php

namespace App\Security;

use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\User;
use App\Enum\Niveau;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client, $request) {
                /** @var \League\OAuth2\Client\Provider\GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();
                $googleId = $googleUser->getId();

                // 1) Try to find by googleId
                $user = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['googleId' => $googleId]);

                if ($user) {
                    return $user;
                }

                // 2) Try to find by email (existing account — link Google)
                $user = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if ($user) {
                    $user->setGoogleId($googleId);
                    $this->entityManager->flush();

                    return $user;
                }

                // 3) Create a new user — type depends on which signup page was used
                $signupType = $request->getSession()->get('google_signup_type');
                $request->getSession()->remove('google_signup_type');

                $user = match ($signupType) {
                    'player' => $this->createPlayer($email, $googleId),
                    'coach'  => $this->createCoach($email, $googleId),
                    default  => $this->createUser($email, $googleId),
                };

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $roles = $token->getUser()->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->router->generate('backoffice_home'));
        }

        return new RedirectResponse($this->router->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        $request->getSession()->getFlashBag()->add('error', $message);

        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    private function createPlayer(string $email, string $googleId): Player
    {
        $player = new Player();
        $player->setEmail($email);
        $player->setGoogleId($googleId);
        $player->setRoles(['ROLE_PLAYER']);
        $player->setPays('Non renseigné');
        $player->setStatut(true);
        $player->setNiveau(Niveau::BEGINNER);
        $this->setRandomPassword($player);

        return $player;
    }

    private function createCoach(string $email, string $googleId): Coach
    {
        $coach = new Coach();
        $coach->setEmail($email);
        $coach->setGoogleId($googleId);
        $coach->setRoles(['ROLE_COACH']);
        $coach->setSpecialite('Non renseigné');
        $coach->setPays('Non renseigné');
        $coach->setDisponibilite(false);
        $this->setRandomPassword($coach);

        return $coach;
    }

    private function createUser(string $email, string $googleId): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setGoogleId($googleId);
        $user->setRoles(['ROLE_USER']);
        $this->setRandomPassword($user);

        return $user;
    }

    private function setRandomPassword(User $user): void
    {
        $randomPassword = bin2hex(random_bytes(32));
        $user->setPassword($this->passwordHasher->hashPassword($user, $randomPassword));
    }
}
