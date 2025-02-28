<?php

namespace App\Dto\Users;

use JMS\Serializer\Annotation\SerializedName;

class Role
{
    #[SerializedName('@id')]
    public string $apiId;
    public int $id;
    public string $code;
}
