<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\AttendEventFormType;
use App\Form\CreateEventFormType;
use App\Form\DeleteEventFormType;
use App\Form\EventInvitationFormType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\EmailSender;
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
    private $userRepository;
    private $renderViewParameters = [];

    public function __construct(EntityManagerInterface $entityManager, EventRepository $eventRepository, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
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
     * @Route("/participation", name="attended")
     */
    public function attendedEvents()
    {
        return $this->render('event/attended.html.twig', [
            'attended_event_list' => $this->getUser()->getEventsAttended(),
        ]);
    }

    /**
     * @Route("/organisation", name="organization")
     */
    public function organizedEvents()
    {
        return $this->render('event/organization.html.twig', [
            'organized_event_list' => $this->eventRepository->findBy(['author' => $this->getUser()->getId()], ['date' => 'ASC']),
        ]);
    }

    /**
     * @Route("", name="list")
     */
    public function eventList()
    {
        return $this->render('event/upcoming_events.html.twig', [
            'upcoming_events_list' => $this->eventRepository->findEventsFromNow(),
        ]);
    }

    /**
     * @Route("/{id}", name="view")
     */
    public function viewEvent(Event $event,Request $request, EmailSender $emailSender)
    {
        $eventInvitationForm = $this->createForm(EventInvitationFormType::class);
        $eventInvitationForm->handleRequest($request);

        if ($eventInvitationForm->isSubmitted() && $eventInvitationForm->isValid()) {
            $emailSender->sendEventInvitationEmail($eventInvitationForm->get('email')->getData(), $event);
            $this->addFlash('success', 'L\'invitation a été envoyé');
        }

        if ($this->isGranted('ROLE_USER', $event)){
            $user = $this->getUser();
            $form = $this->createForm(AttendEventFormType::class, $event);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if (!$event->getAttend()->contains($user)) {
                    $event->addAttend($user);
                    $this->addFlash('success', 'Vous êtes inscrit à l\'événement');
                } elseif ($event->getAttend()->contains($user)) {
                    $event->removeAttend($user);
                    $this->addFlash('success', 'Vous êtes désinscrit de l\'événement');
                }
                $this->entityManager->flush();
            }
            $this->renderViewParameters['attend_form'] = $form->createView();
        }
        $this->renderViewParameters['event'] = $event;
        $this->renderViewParameters['event_invitation_form'] = $eventInvitationForm->createView();

        return $this->render('event/event.html.twig', $this->renderViewParameters);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function editEvent(Event $event, Request $request)
    {
        $this->denyAccessUnlessGranted('EDIT', $event);
        $form = $this->createForm(CreateEventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'événement a été mis à jour');
        }

        return $this->render('event/edit_event.html.twig', [
            'event' => $event,
            'event_edit_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteEvent(Event $event, Request $request)
    {
        $this->denyAccessUnlessGranted('DELETE', $event);
        $form = $this->createForm(DeleteEventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($event);
            $this->entityManager->flush();
            $this->addFlash('danger', 'L\'événement a bien été supprimé');
            return $this->redirectToRoute('event_organization');
        }

        return $this->render('event/delete_event.html.twig', [
            'event' => $event,
            'delete_edit_form' => $form->createView(),
        ]);
    }
}
