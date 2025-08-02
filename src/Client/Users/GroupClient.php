<?php

namespace App\Client\Users;


use App\Client\AbstractClient;
use App\Dto\Users\Group;
use App\Dto\Users\GroupSearch;

/**
 * Class GroupClient
 */
class GroupClient extends AbstractClient
{
    public function find(GroupSearch $groupSearch) 
    {
        return $this->request('GET', '/groups', 'array<'.Group::class.'>', ['query' => $groupSearch->getFilters()] );
    }

    public function getAll()
    {
        return $this->request('GET', '/groups', 'array<'.Group::class.'>');
    }

    public function create(Group $group)
    {
        $this->request('POST', '/groups', null, ['body' => json_encode($group)]);
    }
}
