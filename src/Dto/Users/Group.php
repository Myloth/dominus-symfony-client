<?php

namespace App\Dto\Users;

use Symfony\Component\Serializer\Annotation as Serializer;

class Group
{
    #[Serializer\SerializedName("@id")]
    public ?string $apiId = null;
    public ?int $id = null;
    public ?string $name = null;
    public ?string $slug = null;
    /** @var list<Role>  */
    public ?array $roles = [];
}
