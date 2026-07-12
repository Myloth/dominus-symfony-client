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
    public function find(GroupSearch $groupSearch, int $page = 1, int $itemsPerPage = 10): array
    {
        $query = array_merge($groupSearch->getFilters(), [
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
        ]);
        return $this->requestWithMeta('GET', '/groups', Group::class.'[]', ['query' => $query]);
    }

    public function getAll()
    {
        return $this->request('GET', '/groups', Group::class.'[]');
    }

    public function create(Group $group)
    {
        $this->request('POST', '/groups', null, ['body' => json_encode($group)]);
    }
}
