<?php

namespace App\Controller;

use App\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // page de connexion, accessible depuis le bouton de connexion de l'accueil
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // si l'utilisateur est deja connecte, pas besoin de la page de connexion
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $dernierEmail = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'form' => $this->createForm(LoginFormType::class)->createView(),
            'dernierEmail' => $dernierEmail,
            'error' => $error,
        ]);
    }

    // la deconnexion est geree par le firewall (cf config/packages/security.yaml)
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new AccessDeniedHttpException('La déconnexion est gérée par le pare-feu Symfony.');
    }
}