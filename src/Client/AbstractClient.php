<?php

namespace App\Client;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class AbstractClient
 */
class AbstractClient
{
    public function __construct(
        protected HttpClientInterface $dominusClient,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    ) {

    }

    public function request(string $method, string $uri, ?string $type, array $options = []): mixed
    {
        try {
            $response = $this->dominusClient->request($method, $uri, $options);
        } catch (HttpException $exception) {
            $this->logger->error('Unable to join the API');
            throw $exception;
        }
        if ($response->getStatusCode() >= 400) {
            $this->logger->error(
                'Unable to complete the request',
                [
                    'status_code' => $response->getStatusCode(),
                ]
            );

            throw new HttpException($response->getStatusCode(), 'Unable to complete the request');
        }

        $data = json_decode($response->getContent(), true);
        if (isset($data['member'])) {
            return $this->serializer->deserialize(
                json_encode($data['member']),
                $type ?? 'array',
                'json'
            );
        }

        return $this->serializer->deserialize($response->getContent(), $type ?? 'array', 'json');
    }
}
