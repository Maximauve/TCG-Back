<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    public function getUser(): ?User
    {
        /** @var User|null $user */
        $user = parent::getUser();

        return $user;
    }
}
