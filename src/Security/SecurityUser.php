<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityUser implements UserInterface, EquatableInterface
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        private string $username,
        private array $roles,
        private string $accessToken,
        private ?string $refreshToken = null,
        private ?int $expiresAt = null,
        private ?string $email = null,
        private ?int $id = null,
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?int $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * Checks if the token is expired or will expire within the given margin.
     */
    public function isExpired(int $marginSeconds = 60): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return time() >= ($this->expiresAt - $marginSeconds);
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user instanceof self && $user->getUserIdentifier() === $this->getUserIdentifier();
    }
}
