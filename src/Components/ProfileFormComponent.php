<?php

namespace App\Components;

use App\Entity\Profile;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment as TwigEnvironment;

#[AsLiveComponent('ProfileFormComponent')]
class ProfileFormComponent
{
    use DefaultActionTrait, ComponentWithFormTrait;

    private EntityManagerInterface $entityManager;
    private FormFactoryInterface $formFactory;
    private TokenStorageInterface $tokenStorage;
    private $requestStack;
    private TwigEnvironment $twig;

    #[LiveProp(writable: ['nom', 'prenom', 'pseudo', 'biographie'], fieldName: 'profileData')]
    public Profile $profile;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formFactory, TokenStorageInterface $tokenStorage, RequestStack $requestStack,TwigEnvironment $twig )
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack; 
        $this->twig = $twig;

        $token = $this->tokenStorage->getToken();
        if (null !== $token && $token->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            $user = $token->getUser();
            $this->profile = $user->getProfile() ?? new Profile();
        } else {
            throw new \LogicException('No user is authenticated.');
        }
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->create(ProfileType::class, $this->profile);
    }

    // #[LiveAction]
    // public function saveProfile()
    // {
    //     $this->submitForm();
        
    //     if ($this->getForm()->isSubmitted() && $this->getForm()->isValid()) {
    //         $this->entityManager->persist($this->profile);
    //         $this->entityManager->flush();
    //     }
        
    // }
    #[LiveAction]
    public function saveProfile()
    {
        // Créez le formulaire avec le ProfileType
        $form = $this->formFactory->create(ProfileType::class, $this->profile);
        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($this->profile);
            $this->entityManager->flush();

            // Si la requête est une requête Turbo, renvoyez une réponse Turbo Stream
            if ($this->requestStack->getCurrentRequest()->headers->get('Turbo-Frame')) {
                // Définissez le format de la requête sur turbo_stream
                $this->requestStack->getCurrentRequest()->setRequestFormat('turbo_stream');

                // Renvoyez le HTML pour le Turbo Stream
                return new Response(
                    $this->twig->render('components/profileDisplayComponent.stream.html.twig', [
                        'profile' => $this->profile
                    ]),
                    200,
                    ['Content-Type' => 'text/vnd.turbo-stream.html']
                );
            }

            // Pour une requête non-Turbo, vous pouvez par exemple rediriger l'utilisateur
            return new Response('Le profil a été mis à jour');
        }

        // Si le formulaire n'est pas valide, retournez le formulaire avec les erreurs
        if ($form->isSubmitted() && !$form->isValid()) {
            // Vous pouvez retourner une vue du formulaire avec des erreurs
            // ou traiter les erreurs d'une autre manière
        }

        // Si le formulaire n'a pas été soumis, retournez une vue du formulaire vide
        return $this->twig->render('components/ProfileFormComponent.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

}
