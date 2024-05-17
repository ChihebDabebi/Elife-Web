<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ParkingType; // Import du formulaire pour l'entité Espace
use App\Entity\Parking;
use App\Repository\ParkingRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;




class ParkingController extends AbstractController
{
    #[Route('/parking', name: 'app_parking')]
    public function index(Request $request, ParkingRepository $parkingRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('search');
        $sortBy = $request->query->get('sort_by', 'nom');
        $sortOrder = $request->query->get('sort_order', 'ASC');
    
        $query = $parkingRepository->findBySearchTerm($searchTerm, $sortBy, $sortOrder);
    
        $parkings = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // Get page number from the request, default to 1
            3 // Number of items per page
        );
    
        return $this->render('parking/index.html.twig', [
            'parkings' => $parkings,
            'searchTerm' => $searchTerm,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }
    
    #[Route('/parking/{idParking}/details', name: 'app_parking_details', methods: ['GET'])]
    public function getParkingDetails(int $idParking, ParkingRepository $parkingRepository): JsonResponse
    {
        $parking = $parkingRepository->find($idParking);

        if (!$parking) {
            return new JsonResponse(['error' => 'Parking not found'], Response::HTTP_NOT_FOUND);
        }

        $availablePlaces = $parking->getCapacite() - $parking->getNombreactuelles();

        return new JsonResponse(['availablePlaces' => $availablePlaces]);

    }





    #[Route('/parking/search', name: 'app_parking_search')]
    public function search(Request $request, ParkingRepository $parkingRepository): Response
    {
        // La recherche est gérée dans la méthode index, donc cette méthode peut simplement rediriger vers la page principale.
        return $this->redirectToRoute('app_parking', [
            'search' => $request->query->get('search'),
            'sort_by' => $request->query->get('sort_by'),
            'sort_order' => $request->query->get('sort_order'),
        ]);
    }
    
  
    

    


    #[Route('/parking/sort', name: 'app_parking_sort')]
    public function sort(Request $request, ParkingRepository $parkingRepository): Response
    {
        // Le tri est également géré dans la méthode index, donc cette méthode peut simplement rediriger vers la page principale.
        return $this->redirectToRoute('app_parking', [
            'search' => $request->query->get('search'),
            'sort_by' => $request->query->get('sort_by'),
            'sort_order' => $request->query->get('sort_order'),
        ]);
    }
    

 
    


#[Route('/parking/new', name: 'app_parking_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $parking = new Parking();
    $form = $this->createForm(ParkingType::class, $parking);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($parking);
        $entityManager->flush();

        return $this->redirectToRoute('app_parking', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('parking/new.html.twig', [
        'parking' => $parking,
        'form' => $form,
    ]);
}
#[Route('/parking/{idParking}/edit', name: 'app_parking_edit', methods: ['GET', 'POST'])]
public function edit(int $idParking, Request $request, EntityManagerInterface $entityManager, ParkingRepository $parkingRepository): Response
{
    $parking = $parkingRepository->find($idParking);
    $form = $this->createForm(ParkingType::class, $parking);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_parking', ['idParking' => $idParking], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('parking/edit.html.twig', [
        'parking' => $parking,
        'form' => $form,
    ]);
}

#[Route('/parking/{idParking}', name: 'app_parking_show', methods: ['GET'])]
public function show(int $idParking, ParkingRepository $parkingRepository): Response
{
    $parking = $parkingRepository->find($idParking);

    if (!$parking) {
        throw $this->createNotFoundException('Parking not found');
    }

    return $this->render('parking/show.html.twig', [
        'parking' => $parking,
    ]);
}

#[Route('/parking/{idParking}/delete', name: 'app_parking_delete')]
public function delete(int $idParking, EntityManagerInterface $entityManager, ParkingRepository $parkingRepository): Response
{
    $parking = $parkingRepository->find($idParking);

    if (!$parking) {
        throw $this->createNotFoundException('Parking not found');
    }

    $entityManager->remove($parking);
    $entityManager->flush();

    return $this->redirectToRoute('app_parking', [], Response::HTTP_SEE_OTHER);
}





}