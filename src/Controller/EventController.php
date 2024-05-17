<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Entity\Espace; 
use App\Repository\EspaceRepository; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event_index', methods: ['GET'])]
    public function index(Request $request, EventRepository $eventRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('searchTerm');
        $orderBy = $request->query->get('orderBy', 'date');
        $order = $request->query->get('order', 'ASC');

        $events = $eventRepository->searchAndSort($searchTerm, $orderBy, $order);

        $events = $paginator->paginate(
            $events,
            $request->query->getInt('page', 1), 
            5 
        );

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    
    }
   
    

    #[Route('/event/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EventRepository $eventRepository, EspaceRepository $espaceRepository,ManagerRegistry $doctrine): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository = $doctrine->getRepository(User::class);
        $userId = $request->getSession()->get('user_id',null);
        $connectedUser = $userRepository->find($userId);
        $event->setId($connectedUser);
            $espace = $event->getIdEspace();
            $date = $event->getDate();
            if ($espace && $date && $eventRepository->isSpaceOccupiedAtDate($espace, $date)) {
                $this->addFlash('error', 'L\'espace est déjà occupé à cette date.');
                return $this->redirectToRoute('app_event_new');
            }
    
            $entityManager->persist($event);
            $entityManager->flush();
    
          
            
        }
    
        return $this->renderForm('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
    

    #[Route('/event/{idevent}', name: 'app_event_show', methods: ['GET'])]
    public function show(int $idevent, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($idevent);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/event/{idevent}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(int $idevent, Request $request, EntityManagerInterface $entityManager, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($idevent); 
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
   
    
    #[Route('/event/{idEvent}/delete', name: 'app_event_delete')]
    public function delete(int $idEvent, EntityManagerInterface $entityManager, EventRepository $eventRepository): Response
    {        $event = $eventRepository->find($idEvent);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }




    #[Route('/event/{idevent}/pdf', name: 'app_event_pdf', methods: ['GET'])]
    public function generatePdf(int $idevent, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($idevent);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }
    // Générer le contenu HTML du PDF en utilisant un template Twig

        $html = $this->renderView('event/pdf.html.twig', [
            'event' => $event,
        ]);
    
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
    // Créer une instance de Dompdf

        $dompdf = new Dompdf($options);
    // Charger le contenu HTML dans Dompdf

        $dompdf->loadHtml($html);
    
        $dompdf->setPaper('A4', 'portrait');
    
        $dompdf->render();
    
        $pdfFileName = $event->getTitle() . '.pdf';
    
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="event_details.pdf"',
            ]
        );
    }
    #[Route('/events', name: 'app_event_list')]
    public function list(Request $request , EventRepository $eventRepository,ManagerRegistry $doctrine): Response
    {
        $userRepository = $doctrine->getRepository(User::class);
        $userId = $request->getSession()->get('user_id',null);

        $connectedUser = $userRepository->find($userId);
        

        $events = $this->getDoctrine()->getRepository(Event::class)->findEventByUserId($userId);;
    
        return $this->render('event/list.html.twig', [
            'events' => $events,
        ]);
    }
    
    #[Route('/event/{idevent}/edit-list', name: 'app_event_edit_list', methods: ['GET', 'POST'])]
    public function editList(int $idevent, Request $request, EntityManagerInterface $entityManager, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($idevent);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }
    
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Effectuez les actions nécessaires, par exemple, mettez à jour l'entité dans la base de données
            $entityManager->flush();
    
            // Redirigez vers la page appropriée après l'action réussie
            return $this->redirectToRoute('app_event_list', [], Response::HTTP_SEE_OTHER);
        }
    
        // Si le formulaire n'est pas soumis ou n'est pas valide, affichez à nouveau le formulaire
        return $this->render('event/editlist.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/event/{idEvent}/delete-list', name: 'app_event_delete_list', methods: ['POST'])]
    public function deleteList(int $idEvent, EntityManagerInterface $entityManager, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($idEvent);
    
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }
        $entityManager->remove($event);
        $entityManager->flush();
    
        return $this->redirectToRoute('app_event_list', [], Response::HTTP_SEE_OTHER);
    }
    
    
   

}