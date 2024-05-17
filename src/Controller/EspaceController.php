<?php

namespace App\Controller;

use App\Entity\Espace;
use App\Form\EspaceType;
use App\Repository\EspaceRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class EspaceController extends AbstractController
{
    #[Route('/espace', name: 'app_espace', methods: ['GET'])]
    public function index(Request $request, EspaceRepository $espaceRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('searchTerm');
        $orderBy = $request->query->get('orderBy', 'name');
        $order = $request->query->get('order', 'ASC');
        
        $query = $espaceRepository->searchAndSort($searchTerm, $orderBy, $order);
    
        $espaces = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), 
            5 
        );

        return $this->render('espace/index.html.twig', [
            'espaces' => $espaces,
        ]);
    }

    #[Route('/espace/new', name: 'app_espace_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $espace = new Espace();
        $form = $this->createForm(EspaceType::class, $espace);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($espace);
            $entityManager->flush();

            return $this->redirectToRoute('app_espace_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('espace/add.html.twig', [
            'espace' => $espace,
            'form' => $form,
        ]);
    }

    #[Route('/espace/{idEspace}', name: 'app_espace_show', methods: ['GET'])]
    public function show(int $idEspace, EspaceRepository $espaceRepository): Response
    {
        $espace = $espaceRepository->find($idEspace);
        if (!$espace) {
            throw $this->createNotFoundException('Espace not found');
        }

        return $this->render('espace/show.html.twig', [
            'espace' => $espace,
        ]);
    }

    #[Route('/espace/{idEspace}/edit', name: 'app_espace_edit', methods: ['GET', 'POST'])]
    public function edit(int $idEspace, Request $request, EntityManagerInterface $entityManager, EspaceRepository $espaceRepository): Response
    {
        $espace = $espaceRepository->find($idEspace);
        if (!$espace) {
            throw $this->createNotFoundException('Espace not found');
        }

        $form = $this->createForm(EspaceType::class, $espace);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_espace_edit', ['idEspace' => $idEspace], Response::HTTP_SEE_OTHER);
        }

        return $this->render('espace/edit.html.twig', [
            'espace' => $espace,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/espace/{idEspace}/delete', name: 'app_espace_delete')]
    public function delete(int $idEspace, EntityManagerInterface $entityManager, EspaceRepository $espaceRepository): Response
    {
        $espace = $espaceRepository->find($idEspace);
        if (!$espace) {
            throw $this->createNotFoundException('Espace not found');
        }

        $entityManager->remove($espace);
        $entityManager->flush();

        return $this->redirectToRoute('app_espace', [], Response::HTTP_SEE_OTHER);
    }

#[Route('/espace/{idEspace}/calendar', name: 'app_espace_calendar', methods: ['GET'])]
public function calendar(int $idEspace, EspaceRepository $espaceRepository): Response
{
    $espace = $espaceRepository->find($idEspace);
    
    // Récupération de tous les événements associés à cet espace
    $events = $espace->getEvents(); 
    
    // Initialisation d'un tableau pour stocker les événements au format JSON
    $eventsForJson = [];
    
    // Conversion des événements en format JSON pour une utilisation avec FullCalendar
    foreach ($events as $event) {
        $eventsForJson[] = [
            'title' => $event->getTitle(),
            'start' => $event->getDate()->format('Y-m-d'), 
        ];
    }
    
    return $this->render('espace/calendar.html.twig', [
        'espace' => $espace, 
        'events' => json_encode($eventsForJson), // Passage des événements au format JSON au template Twig
    ]);
}

 }
    
    

    
