<?php

namespace App\Security\OAuth2;

use App\Security\SecurityUser;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class OAuth2TokenManager
{
    public function __construct(
        private readonly Security $security,
        private readonly OAuth2Provider $provider,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Returns a valid access token for the authenticated user,
     * automatically refreshing it if it has expired or is about to expire.
     */
    public function getValidAccessToken(): ?string
    {
        $user = $this->security->getUser();
        if (!$user instanceof SecurityUser) {
            return null;
        }

        if ($user->isExpired(marginSeconds: 60) && $user->getRefreshToken()) {
            return $this->refreshToken($user);
        }

        return $user->getAccessToken();
    }

    /**
     * Refreshes the access token using the user's refresh token.
     */
    public function refreshToken(SecurityUser $user): ?string
    {
        $refreshToken = $user->getRefreshToken();
        if (!$refreshToken) {
            return null;
        }

        try {
            $newToken = $this->provider->refreshAccessToken($refreshToken);
            $user->setAccessToken($newToken->getToken());
            if ($newToken->getRefreshToken()) {
                $user->setRefreshToken($newToken->getRefreshToken());
            }
            $user->setExpiresAt($newToken->getExpires());

            // Update user roles and properties from refreshed token payload if available
            $parts = explode('.', $newToken->getToken());
            if (count($parts) === 3) {
                $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if (is_array($payload)) {
                    if (isset($payload['roles']) && is_array($payload['roles'])) {
                        $roles = array_values(array_unique(array_merge(['ROLE_USER'], $payload['roles'])));
                        $user->setRoles($roles);
                    }
                    if (isset($payload['email'])) {
                        $user->setEmail((string) $payload['email']);
                    }
                    if (isset($payload['user_id'])) {
                        $user->setId((int) $payload['user_id']);
                    }
                }
            }

            $this->logger->info('OAuth2 access token successfully refreshed for user "{username}".', [
                'username' => $user->getUserIdentifier(),
            ]);

            return $user->getAccessToken();
        } catch (\Throwable $e) {
            $this->logger->error('Failed to refresh OAuth2 access token: {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return null;
        }
    }
}
