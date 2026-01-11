<?php

namespace App\Client\Users;

use App\Client\AbstractClient;
use App\Dto\Users\Role;

class RoleClient extends AbstractClient
{
    public function getAll()
    {
        return $this->request('GET', 'roles', Role::class.'[]');
    }
}