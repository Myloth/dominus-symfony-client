<?php

namespace App\Client;

use App\Security\OAuth2\OAuth2TokenManager;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

#[AsDecorator(decorates: 'dominus_client')]
class OAuth2DecoratingHttpClient implements HttpClientInterface, ResetInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private readonly OAuth2TokenManager $tokenManager,
    ) {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $token = $this->tokenManager->getValidAccessToken();

        if ($token !== null) {
            $headers = $options['headers'] ?? [];
            if (!isset($headers['Authorization']) && !isset($headers['authorization'])) {
                $headers['Authorization'] = 'Bearer ' . $token;
                $options['headers'] = $headers;
            }
        }

        return $this->client->request($method, $url, $options);
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    public function withOptions(array $options): static
    {
        return new static($this->client->withOptions($options), $this->tokenManager);
    }

    public function reset(): void
    {
        if ($this->client instanceof ResetInterface) {
            $this->client->reset();
        }
    }
}
