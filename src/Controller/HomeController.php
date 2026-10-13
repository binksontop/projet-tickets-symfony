<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketPublicType;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Page d'accueil : elle est visible par tous les visiteurs et contient
// le formulaire de création de ticket ainsi que le bouton de connexion.
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $ticket = new Ticket();

        // le nouveau ticket prend l'etat "Nouveau", defini dans les donnees de reference.
        // cet etat est fixe AVANT la soumission : le visiteur ne doit pas pouvoir le choisir,
        // mais la validation de l'entite exige qu'un etat soit defini
        $etatNouveau = $etatRepository->findOneBy(['libelle' => 'Nouveau']);

        if ($etatNouveau === null) {
            // l'etat de reference a ete supprime par l'administrateur : on prend le premier
            $etatNouveau = $etatRepository->findOneBy([], ['id' => 'ASC']);
        }

        $ticket->setEtat($etatNouveau);

        $form = $this->createForm(TicketPublicType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Votre ticket a bien été enregistré. Notre équipe va le traiter.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form,
        ]);
    }
}