<?php

namespace App\Client\Users;

use App\Dto\Users\User;
use App\Dto\Users\UserSearch;
use App\Client\AbstractClient;

class UserClient extends AbstractClient
{
    public function getAll(): array
    {
        return $this->request('GET', 'users', User::class.'[]');
    }

    public function find(UserSearch $userSearch): array
    {
        return $this->request('GET', 'users', User::class.'[]', ['query' => $userSearch->getFilters()] );
    }

    public function create(User $user): User
    {
        return $this->request('POST', 'users', User::class, ['body' => json_encode($user)]);
    }
}