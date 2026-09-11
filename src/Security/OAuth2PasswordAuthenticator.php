<?php

namespace App\Security;

use App\Security\OAuth2\OAuth2Provider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class OAuth2PasswordAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly OAuth2Provider $oauth2Provider,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $username = trim((string) $request->request->get('_username', ''));
        $password = (string) $request->request->get('_password', '');
        $csrfToken = (string) $request->request->get('_csrf_token', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        if ($username === '' || $password === '') {
            throw new BadCredentialsException('Veuillez renseigner votre identifiant et mot de passe.');
        }

        try {
            $accessToken = $this->oauth2Provider->getAccessTokenFromPassword($username, $password);
        } catch (IdentityProviderException $e) {
            $this->logger->warning('OAuth2 login failed for user "{username}": {error}', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            throw new BadCredentialsException('Identifiants invalides.');
        } catch (\Throwable $e) {
            $this->logger->error('OAuth2 server communication error during login: {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            throw new CustomUserMessageAuthenticationException('Impossible de joindre le serveur d\'authentification.');
        }

        // Determine roles and user details from JWT token payload extra claims and scopes
        $roles = ['ROLE_USER'];
        $email = null;
        $id = null;
        $parts = explode('.', $accessToken->getToken());
        if (count($parts) === 3) {
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            if (is_array($payload)) {
                if (isset($payload['roles']) && is_array($payload['roles'])) {
                    $roles = array_merge($roles, $payload['roles']);
                }

                if (isset($payload['scopes'])) {
                    $scopes = (array) $payload['scopes'];
                    if (in_array('administration', $scopes, true)) {
                        $roles[] = 'ROLE_ADMIN';
                    }
                }

                $email = isset($payload['email']) ? (string) $payload['email'] : null;
                $id = isset($payload['user_id']) ? (int) $payload['user_id'] : null;
            }
        }

        $roles = array_values(array_unique($roles));

        $user = new SecurityUser(
            username: $username,
            roles: $roles,
            accessToken: $accessToken->getToken(),
            refreshToken: $accessToken->getRefreshToken(),
            expiresAt: $accessToken->getExpires(),
            email: $email,
            id: $id,
        );

        $badges = [];
        if ($csrfToken !== '') {
            $badges[] = new CsrfTokenBadge('authenticate', $csrfToken);
        }

        return new SelfValidatingPassport(
            new UserBadge($username, static fn () => $user),
            $badges
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('admin_users_list'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
