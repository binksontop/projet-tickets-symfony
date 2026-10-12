<?php

namespace App\Controller;

use App\Entity\Etat;
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
        $form = $this->createForm(TicketPublicType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // le nouvel ticket prend l'etat "Nouveau", defini dans les donnees de reference
            $etatNouveau = $etatRepository->findOneBy(['libelle' => 'Nouveau']);

            if ($etatNouveau === null) {
                // l'etat de reference a ete supprime par l'administrateur : on prend le premier
                $etatNouveau = $etatRepository->findOneBy([], ['id' => 'ASC']);
            }

            $ticket->setEtat($etatNouveau);

            $entityManager->persist($ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Votre ticket a bien été enregistré. Notre équipe va le traiter.');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form,
        ]);
    }
}