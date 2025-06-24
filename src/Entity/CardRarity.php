<?php

namespace App\Entity;

enum CardRarity: string
{
    case COMMON = 'common';
    case UNCOMMON = 'uncommon';
    case RARE = 'rare';
    case EPIC = 'epic';
    case MYTHIC = 'mythic';
    case SPECIAL = 'special';
    case PROMO = 'promo';
    case LIMITED = 'limited';
}