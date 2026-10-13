<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Etat;
use App\Entity\Responsable;
use App\Entity\Ticket;
use App\Form\CategorieType;
use App\Form\EtatType;
use App\Form\ResponsableType;
use App\Form\TicketAdminType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// L'administrateur peut creer et modifier tous les types de donnees :
// les etats, les categories, les responsables et les tickets.
#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    // ------------------------------------------------------------
    // Gestion des etats
    // ------------------------------------------------------------

    #[Route('/etats', name: 'app_admin_etats')]
    public function etats(EntityManagerInterface $entityManager): Response
    {
        $etats = $entityManager->getRepository(Etat::class)->findAll();

        return $this->render('admin/referentiel/index.html.twig', [
            'titre' => 'Les états',
            'entites' => $etats,
            'nomRoute' => 'app_admin_etats',
        ]);
    }

    #[Route('/etats/nouveau', name: 'app_admin_etats_new')]
    public function etatNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->gererReferentiel(EtatType::class, new Etat(), $request, $entityManager,
            'admin/referentiel/form.html.twig', 'app_admin_etats',
            "L'état a bien été créé.", "Nouvel état");
    }

    #[Route('/etats/{id}/modifier', name: 'app_admin_etats_edit', requirements: ['id' => '\d+'])]
    public function etatEdit(Request $request, Etat $etat, EntityManagerInterface $entityManager): Response
    {
        return $this->gererReferentiel(EtatType::class, $etat, $request, $entityManager,
            'admin/referentiel/form.html.twig', 'app_admin_etats',
            "L'état a bien été modifié.", "Modifier l'état");
    }

    // ------------------------------------------------------------
    // Gestion des categories
    // ------------------------------------------------------------

    #[Route('/categories', name: 'app_admin_categories')]
    public function categories(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Categorie::class)->findAll();

        return $this->render('admin/referentiel/index.html.twig', [
            'titre' => 'Les catégories',
            'entites' => $categories,
            'nomRoute' => 'app_admin_categories',
        ]);
    }

    #[Route('/categories/nouveau', name: 'app_admin_categories_new')]
    public function categorieNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->gererReferentiel(CategorieType::class, new Categorie(), $request, $entityManager,
            'admin/referentiel/form.html.twig', 'app_admin_categories',
            'La catégorie a bien été créée.', 'Nouvelle catégorie');
    }

    #[Route('/categories/{id}/modifier', name: 'app_admin_categories_edit', requirements: ['id' => '\d+'])]
    public function categorieEdit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        return $this->gererReferentiel(CategorieType::class, $categorie, $request, $entityManager,
            'admin/referentiel/form.html.twig', 'app_admin_categories',
            'La catégorie a bien été modifiée.', 'Modifier la catégorie');
    }

    // ------------------------------------------------------------
    // Gestion des responsables
    // ------------------------------------------------------------

    #[Route('/responsables', name: 'app_admin_responsables')]
    public function responsables(EntityManagerInterface $entityManager): Response
    {
        $responsables = $entityManager->getRepository(Responsable::class)->findAll();

        return $this->render('admin/referentiel/index.html.twig', [
            'titre' => 'Les responsables',
            'entites' => $responsables,
            'nomRoute' => 'app_admin_responsables',
        ]);
    }

    #[Route('/responsables/nouveau', name: 'app_admin_responsables_new')]
    public function responsableNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->gererReferentiel(ResponsableType::class, new Responsable(), $request, $entityManager,
            'admin/referentiel/form.html.twig', 'app_admin_responsables',
            'Le responsable a bien été créé.', 'Nouveau responsable');
    }

    #[Route('/responsables/{id}/modifier', name: 'app_admin_responsables_edit', requirements: ['id' => '\d+'])]
    public function responsableEdit(Request $request, Responsable $responsable, EntityManagerInterface $entityManager): Response
    {
        return $this->gererReferentiel(ResponsableType::class, $responsable, $request, $entityManager,
            'admin/referentiel/form.html.twig', 'app_admin_responsables',
            'Le responsable a bien été modifié.', 'Modifier le responsable');
    }

    // ------------------------------------------------------------
    // Gestion des tickets (l'administrateur peut tout modifier)
    // ------------------------------------------------------------

    #[Route('/tickets', name: 'app_admin_tickets')]
    public function tickets(TicketRepository $ticketRepository): Response
    {
        return $this->render('admin/ticket/list.html.twig', [
            'tickets' => $ticketRepository->findBy([], ['dateOuverture' => 'DESC']),
        ]);
    }

    #[Route('/tickets/nouveau', name: 'app_admin_ticket_new')]
    public function ticketNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketAdminType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // regle metier : un ticket ferme prend une date de cloture
            $this->cloturerSiBesoin($ticket);

            $entityManager->persist($ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Le ticket a bien été créé.');

            return $this->redirectToRoute('app_admin_tickets');
        }

        return $this->render('admin/ticket/form.html.twig', [
            'form' => $form,
            'titre' => 'Nouveau ticket',
        ]);
    }

    #[Route('/tickets/{id}/modifier', name: 'app_admin_ticket_edit', requirements: ['id' => '\d+'])]
    public function ticketEdit(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketAdminType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->cloturerSiBesoin($ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Le ticket a bien été modifié.');

            return $this->redirectToRoute('app_admin_tickets');
        }

        return $this->render('admin/ticket/form.html.twig', [
            'form' => $form,
            'titre' => 'Modifier le ticket n° ' . $ticket->getId(),
        ]);
    }

    // ------------------------------------------------------------
    // Methodes internes partagees par les CRUD
    // ------------------------------------------------------------

    // gere la creation et la modification d'un etat, d'une categorie ou d'un responsable
    private function gererReferentiel(string $formClass, $entite, Request $request,
        EntityManagerInterface $entityManager, string $template, string $route,
        string $message, string $titre): Response
    {
        $form = $this->createForm($formClass, $entite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($entite);
            $entityManager->flush();

            $this->addFlash('success', $message);

            return $this->redirectToRoute($route);
        }

        return $this->render($template, [
            'form' => $form,
            'titre' => $titre,
            'routeRetour' => $route,
        ]);
    }

    // renseigne la date de cloture quand le ticket passe a l'etat "Fermé"
    private function cloturerSiBesoin(Ticket $ticket): void
    {
        if ($ticket->getEtat()->getLibelle() === 'Fermé' && $ticket->getDateCloture() === null) {
            $ticket->setDateCloture(new \DateTimeImmutable());
        }
    }
}