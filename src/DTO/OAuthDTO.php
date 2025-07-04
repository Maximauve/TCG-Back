<?php

namespace App\DTO;

use App\Enum\ProviderEnum;
use Symfony\Component\Validator\Constraints as Assert;

class OAuthDTO
{
    #[Assert\NotBlank(message: "oauth.provider.not_blank")]
    public ProviderEnum $provider;

    #[Assert\NotBlank(message: "oauth.providerId.not_blank")]
    public string $providerId;

    #[Assert\NotBlank(message: "oauth.email.not_blank")]
    public string $email;

    #[Assert\NotBlank(message: "oauth.name.not_blank")]
    public string $name;

    public ?string $firstName = null;

    public ?string $lastName = null;
}
