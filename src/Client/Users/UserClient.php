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

    public function find(UserSearch $userSearch, int $page = 1, int $itemsPerPage = 10): array
    {
        $query = array_merge($userSearch->getFilters(), [
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
        ]);

        return  $this->requestWithMeta('GET', 'users', User::class.'[]', ['query' => $query] );
    }

    public function create(User $user): User
    {
        return $this->request('POST', 'users', User::class, ['body' => json_encode($user)]);
    }
}