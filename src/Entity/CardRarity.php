<?php

namespace App\Entity;

enum CardRarity: string
{
    case COMMON = 'card.common';
    case UNCOMMON = 'card.uncommon';
    case RARE = 'card.rare';
    case EPIC = 'card.epic';
    case MYTHIC = 'card.mythic';
    case SPECIAL = 'card.special';
    case PROMO = 'card.promo';
    case LIMITED = 'card.limited';
}