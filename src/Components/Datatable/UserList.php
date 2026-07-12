<?php

namespace App\Components\Datatable;

use App\Client\Users\GroupClient;
use App\Client\Users\UserClient;
use App\Dto\Users\UserSearch;
use App\Form\Search\User\UserSearchType;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;

#[AsLiveComponent('UserList')]
class UserList extends AbstractDatatableBase
{
    use ComponentWithFormTrait;

    public function __construct(
        private readonly GroupClient $groupClient,
        private readonly UserClient $userClient,
    ) {

    }
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UserSearchType::class, new UserSearch(), $this->getFormOptions());
    }

    protected function getApiResult(): array
    {
        if ($this->apiResult === null) {
            $this->submitForm();
            $groupSearch = $this->getForm()->getData();
            $this->apiResult = $this->userClient->find($groupSearch, $this->page, $this->itemsPerPage);
        }

        return $this->apiResult;
    }

    public function getHeaders(): array
    {
        return [
            'users.listing.header.id',
            'users.listing.header.name',
            "users.listing.header.group",
            "users.listing.header.actions",
        ];
    }

    private function getFormOptions(): array
    {
        $groups = $this->groupClient->getAll();
        $groupOptions = [];
        array_walk($groups, function ($item) use (&$groupOptions) {
            $groupOptions[$item->name] = $item->apiId;
        });

        return [
            'groups' => $groupOptions,
        ];
    }
}