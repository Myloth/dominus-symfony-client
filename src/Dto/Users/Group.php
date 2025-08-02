<?php

namespace App\Dto\Users;

use JMS\Serializer\Annotation as JMS;

class Group
{
    #[JMS\SerializedName("@id")]
    public ?string $apiId = null;
    #[JMS\Type("integer")]
    public ?int $id = null;
    #[JMS\Type("string")]
    public ?string $name = null;
    #[JMS\Type("string")]
    public ?string $slug = null;
    #[JMS\Type("array<App\Dto\Users\Role>")]
    public ?array $roles = [];
}
