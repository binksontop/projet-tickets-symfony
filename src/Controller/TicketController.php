<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Ticket;
use App\Form\TicketStatutType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Les pages de tickets demandent une connexion (cf access_control dans security.yaml).
// Le personnel de l'agence et l'administrateur y ont accès.
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class TicketController extends AbstractController
{
    // liste de tous les tickets
    #[Route('/tickets', name: 'app_tickets')]
    public function liste(TicketRepository $ticketRepository): Response
    {
        $tickets = $ticketRepository->findBy([], ['dateOuverture' => 'DESC']);

        return $this->render('ticket/list.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    // detail d'un ticket
    #[Route('/tickets/{id}', name: 'app_ticket_show', requirements: ['id' => '\d+'])]
    public function show(Ticket $ticket): Response
    {
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    // modification du statut du ticket par le personnel de l'agence
    #[Route('/tickets/{id}/statut', name: 'app_ticket_statut', requirements: ['id' => '\d+'])]
    public function statut(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketStatutType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // quand le ticket passe a l'etat "Fermé", on renseigne la date de cloture
            // si elle n'est pas encore definie
            if ($ticket->getEtat()->getLibelle() === 'Fermé' && $ticket->getDateCloture() === null) {
                $ticket->setDateCloture(new \DateTimeImmutable());
            }

            $entityManager->flush();

            $this->addFlash('success', "L'état du ticket a bien été modifié.");

            return $this->redirectToRoute('app_tickets');
        }

        return $this->render('ticket/statut.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }
}