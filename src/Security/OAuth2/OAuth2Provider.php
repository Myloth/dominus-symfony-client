<?php

namespace App\Security\OAuth2;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class OAuth2Provider
{
    private AbstractProvider $provider;

    public function __construct(
        #[Autowire('%env(DOMINUS_API_ENDPOINT)%')]
        private readonly string $apiEndpoint,
        #[Autowire('%env(default::OAUTH_CLIENT_ID)%')]
        private readonly string $clientId = 'dominus-client',
        #[Autowire('%env(default::OAUTH_CLIENT_SECRET)%')]
        private readonly string $clientSecret = 'dominus-secret',
        #[Autowire('%env(default::OAUTH_SCOPES)%')]
        private readonly string $defaultScope = 'administration',
        ?AbstractProvider $provider = null,
    ) {
        $baseUrl = rtrim($this->apiEndpoint, '/');

        $this->provider = $provider ?? new GenericProvider([
            'clientId'                => $this->clientId,
            'clientSecret'            => $this->clientSecret,
            'urlAuthorize'            => $baseUrl . '/authorize',
            'urlAccessToken'          => $baseUrl . '/token',
            'urlResourceOwnerDetails' => $baseUrl . '/users/me',
            'scopes'                  => $this->defaultScope,
        ]);
    }

    /**
     * Obtains an access token using Resource Owner Password Credentials Grant.
     *
     * @throws IdentityProviderException
     */
    public function getAccessTokenFromPassword(string $username, string $password, ?string $scope = null): AccessTokenInterface
    {
        $options = [
            'username' => $username,
            'password' => $password,
        ];

        $effectiveScope = $scope ?? $this->defaultScope;
        if (!empty($effectiveScope)) {
            $options['scope'] = $effectiveScope;
        }

        return $this->provider->getAccessToken('password', $options);
    }

    /**
     * Refreshes an expired access token using the Refresh Token Grant.
     *
     * @throws IdentityProviderException
     */
    public function refreshAccessToken(string $refreshToken): AccessTokenInterface
    {
        return $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken,
        ]);
    }

    public function getProvider(): AbstractProvider
    {
        return $this->provider;
    }
}
