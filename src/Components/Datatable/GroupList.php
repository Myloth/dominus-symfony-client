<?php

namespace App\Components\Datatable;

use App\Client\Users\GroupClient;
use App\Client\Users\RoleClient;
use App\Dto\Users\Group;
use App\Dto\Users\GroupSearch;
use App\Form\Search\User\GroupSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'GroupList')]
class GroupList extends AbstractDatatableBase
{
    use ComponentWithFormTrait;

    public function __construct(
        private readonly GroupClient $client,
        private readonly RoleClient $roleClient,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(GroupSearchType::class, new GroupSearch(), $this->generateFormOptions());
    }

    protected function getApiResult(): array
    {
        if ($this->apiResult === null) {
            $this->submitForm();
            $groupSearch = $this->getForm()->getData();
            $this->apiResult = $this->client->find($groupSearch, $this->page, $this->itemsPerPage);
        }

        return $this->apiResult;
    }

    public function getHeaders(): array
    {
        return [
            'groups.listing.header.id',
            'groups.listing.header.name',
            'groups.listing.header.roles',
            'groups.listing.header.actions',
        ];
    }

    private function generateFormOptions(): array
    {
        $formOptions = ['roles' => []];
        $roles = $this->roleClient->getAll();
        array_walk($roles, function($value) use (&$formOptions) {
            $formOptions['roles'][$value->apiId] = $value->code;
        });

        return $formOptions;
    }
}