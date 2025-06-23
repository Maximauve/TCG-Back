<?php

namespace App\Enum;

enum ProviderEnum: string
{
    case GOOGLE = 'google';
    case DISCORD = 'discord';
    case TWITCH = 'twitch';
}
