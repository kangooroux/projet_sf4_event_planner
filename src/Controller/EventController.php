<?php

namespace App\Controller;

use App\Form\CreateEventFormType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/event", name="event_")
 */
class EventController extends AbstractController
{
    private $entityManager;
    private $eventRepository;

    public function __construct(EntityManagerInterface $entityManager, EventRepository $eventRepository)
    {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route("/creer", name="create")
     */
    public function createEvent(Request $request)
    {
        $form = $this->createForm(CreateEventFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $event->setAuthor($this->getUser());
            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $this->addFlash('success', 'Evénement créé avec succès');
            return $this->redirectToRoute('event_organization');
        }

        return $this->render('event/create_event.html.twig', [
            'event_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/participation", name="attend")
     */
    public function attendedEvents()
    {

    }

    /**
     * @Route("/organisation", name="organization")
     */
    public function organizedEvents()
    {
        return $this->render('event/organization.html.twig', [
            'organized_event_list' => $this->eventRepository->findBy(['author' => $this->getUser()->getId()]),
        ]);
    }
}
