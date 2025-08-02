<?php

namespace App\Dto\Users;

use JMS\Serializer\Annotation as JMS;

class Role
{
    #[JMS\SerializedName('@id')]
    #[JMS\Type('string')]
    public ?string $apiId = null;
    #[JMS\Type('integer')]
    public ?int $id = null;
    #[JMS\Type('string')]
    public ?string $code = null;
}
