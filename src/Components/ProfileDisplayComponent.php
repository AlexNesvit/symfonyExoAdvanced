<?php

namespace App\Components;

use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsLiveComponent('ProfileDisplayComponent')]
class ProfileDisplayComponent
{
    use DefaultActionTrait; // Utilisation du trait pour une action par défaut

    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;

    #[LiveProp]
    public Profile $profile;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;

        $token = $this->tokenStorage->getToken();
        if (null !== $token && $token->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            $user = $token->getUser();
            $this->profile = $user->getProfile() ?? new Profile();
        } else {
            throw new \LogicException('No user is authenticated.');
        }
    }
}
