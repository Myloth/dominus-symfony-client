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

        if ($response->getStatusCode() === 204 || $type === null) {
            return null;
        }

        $content = $response->getContent();
        if ($content === '') {
            return null;
        }

        $data = json_decode($content, true);
        if (isset($data['member'])) {
            return $this->serializer->deserialize(
                json_encode($data['member']),
                $type ?? 'array',
                'json'
            );
        }

        return $this->serializer->deserialize($content, $type ?? 'array', 'json');
    }

    public function requestPaginated(string $method, string $uri, string $itemClass, array $options = []): array
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

        $content = $response->getContent();
        $data = json_decode($content, true) ?? [];
        $members = $data['member'] ?? [];
        $totalItems = (int) ($data['totalItems'] ?? $data['hydra:totalItems'] ?? count($members));

        $items = $this->serializer->deserialize(
            json_encode($members),
            $itemClass . '[]',
            'json'
        );

        return [
            'items' => $items,
            'totalItems' => $totalItems,
            'raw' => $data,
        ];
    }
}
