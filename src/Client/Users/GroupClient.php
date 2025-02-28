<?php

namespace App\Client\Users;


use App\Client\AbstractClient;
use App\Dto\Users\Group;

/**
 * Class GroupClient
 */
class GroupClient extends AbstractClient
{
    public function getAll()
    {
        return $this->request('GET', '/groups', 'array<'.Group::class.'>');
    }

    public function create(Group $group)
    {
        $this->request('POST', '/groups', null, ['body' => json_encode($group)]);
    }
}
