<?php

namespace App\Dto\Users;

use Symfony\Component\Serializer\Annotation as Serializer;

class User
{
    #[Serializer\SerializedName('@id')]
    public ?string $apiId = null;

    public ?int $id = null;

    public ?string $username = null;

    public ?string $email = null;

    /** @var list<Group> */
    public array $groups = [];

    public ?string $password = null;
    
}

