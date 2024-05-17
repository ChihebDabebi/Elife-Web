<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AppartementType;
use App\Entity\Appartement;
use App\Repository\AppartementRepository;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use App\Form\StatFilterType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppartementController extends AbstractController
{
    #[Route('/home', name: 'app_d')]
    public function index2(Request $request,ManagerRegistry $doctrine): Response
    {

        $userId = $request->getSession()->get('user_id',null);
       


        return $this->render('baseFront.html.twig', [
            'controller_name' => 'AppartementController',
            'id'=>$userId,
        ]);
    }
    
    #[Route('/', name: 'app_d3')]
    public function index3(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id',null);
    
        return $this->render('index.html.twig', [
            'controller_name' => 'AppartementController',
            'id'=>$userId,
        ]); 
      
    }
    #[Route('/appartement', name: 'app_appartement_index', methods: ['GET'])]
    public function index(Request $request, AppartementRepository $appartementRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $sortBy = $request->query->get('sort_by', 'numappartement');
        $search = $request->query->get('search', '');
        $criteria = [];
        $orderBy = [];
    
        // Si une recherche par nom de résident ou numéro d'appartement est spécifiée
        if (!empty($search)) {
            $criteria = [
                'nomresident' => $search,
            ];
        }
    
        // Déterminez l'ordre de tri
        switch ($sortBy) {
            case 'numappartement':
                $orderBy = ['numappartement' => 'ASC'];
                break;
            case 'nomResident':
                $orderBy = ['nomresident' => 'ASC']; // Modifier si le champ réel est différent
                break;
            // Ajoutez d'autres cas de tri si nécessaire
            default:
                // Défaut: tri par numéro d'appartement
                $orderBy = ['numappartement' => 'ASC'];
                break;
        }
    
        // Utilisez la méthode findBy avec les critères et l'ordre de tri
        $appartements = $appartementRepository->findBy($criteria, $orderBy);
    
        // Rendre la vue avec les résultats de la recherche et du tri
        return $this->render('appartement/index.html.twig', [
            'appartements' => $appartements,
            'search' => $search, // Pour maintenir la valeur de recherche dans le formulaire
        ]);
    }
    #[Route('/appartement/user', name: 'app_appartement_for_user')]
    public function getAppartementsForUser(Request $request ,ManagerRegistry $doctrine, AppartementRepository $appartementRepository): Response
    {

        $userRepository = $doctrine->getRepository(User::class);
        $userId = $request->getSession()->get('user_id',null);

        $connectedUser = $userRepository->find($userId);
        if (!$connectedUser) {
            throw $this->createNotFoundException('User not found');
        }

        // Appeler la méthode pour récupérer les appartements de l'utilisateur spécifique
        $appartements = $appartementRepository->findAppartementsByUser($userId);

        // Afficher les appartements récupérés dans un template Twig
        return $this->render('appartement/indexfront.html.twig', [
            'user' => $connectedUser,
            'appartements' => $appartements,
            'id'=>$userId
        ]);
    }
    
    
  
    #[Route('/appartement/new', name: 'app_appartement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $appartement = new Appartement();
        $form = $this->createForm(AppartementType::class, $appartement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appartement);
            $entityManager->flush();

            return $this->redirectToRoute('app_appartement_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('/appartement/add.html.twig', [
            'appartement' => $appartement,
            'form' => $form,
        ]);
    }

    #[Route('/appartement/{idAppartement}', name: 'app_appartement_show', methods: ['GET'])]
    public function show(int $idAppartement, AppartementRepository $appartementRepository, FactureRepository $factureRepository): Response
    {
        $appartement = $appartementRepository->find($idAppartement);
        if (!$appartement) {
            throw $this->createNotFoundException('Appartement not found');
        }
    
        // Récupérer les factures liées à cet appartement
        $factures = $factureRepository->findBy(['idAppartement' => $idAppartement]);
    
        return $this->render('appartement/show.html.twig', [
            'appartement' => $appartement,
            'factures' => $factures, // Passer les factures au modèle Twig
        ]);
    }
    #[Route('/appartement/{idAppartement}/edit', name: 'app_appartement_edit', methods: ['GET', 'POST'])]
    public function edit(int $idAppartement, Request $request, EntityManagerInterface $entityManager, AppartementRepository $appartementRepository): Response
    {
        $appartement = $appartementRepository->find($idAppartement);
        $form = $this->createForm(AppartementType::class, $appartement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            return $this->redirectToRoute('app_appartement_edit', ['idAppartement' => $idAppartement], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('appartement/edit.html.twig', [
            'appartement' => $appartement,
            'form' => $form->createView(), // Appel de createView() sur le formulaire
        ]);
    }
    

    
    #[Route('/appartement/{idAppartement}/delete', name: 'app_appartement_delete')]
    public function delete(int $idAppartement, EntityManagerInterface $entityManager, AppartementRepository $appartementRepository): Response
    {
        $appartement = $appartementRepository->find($idAppartement);
    
        if (!$appartement) {
            throw $this->createNotFoundException('Appartement not found');
        }
    
        $entityManager->remove($appartement);
        $entityManager->flush();
    
        return $this->redirectToRoute('app_appartement_index', [], Response::HTTP_SEE_OTHER);
    }
   
    
  
    #[Route('/appartement/{idAppartement}/details', name: 'app_appartement_details', methods: ['GET'])]
    public function details(int $idAppartement, AppartementRepository $appartementRepository): Response  
{
    $appartement = $appartementRepository->find($idAppartement);
    if (!$appartement) {
        throw $this->createNotFoundException('Appartement not found');
    }

    return $this->render('appartement/details.html.twig', [
        'appartement' => $appartement,
    ]);
}
#[Route('/stat', name: 'stat')]
public function stat(Request $request, FactureRepository $factureRepository): Response
{
    // Créer le formulaire de filtre
    $form = $this->createForm(StatFilterType::class);
    $form->handleRequest($request);

    // Initialiser les variables pour les statistiques
    $factures = [];
    $consommationTotale = 0;
    $montantTotal = 0;
    $type = null;
    $startDate = null;
    $endDate = null;
    $nbrEtage = null;

    // Si le formulaire est soumis et valide, traiter les données
    if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();
        $type = $data['type'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];
        $nbrEtage = $data['nbrEtage']; // Ajout du champ pour le nombre d'étages

        // Récupérer les factures en fonction du type, de la plage de dates et du nombre d'étages
        $factures = $factureRepository->findByTypeAndDateRange($type, $startDate, $endDate, $nbrEtage);

        // Calculer les statistiques à partir des factures filtrées
        foreach ($factures as $facture) {
            $consommationTotale += $facture->getConsommation();
            $montantTotal += $facture->getMontant();
        }
    }

    // Rendre la vue avec les données de statistiques et les factures filtrées
    return $this->render('appartement/stats.html.twig', [
        'factures' => $factures,
        'consommationTotale' => $consommationTotale,
        'montantTotal' => $montantTotal,
        'form' => $form->createView(), // Passer le formulaire à la vue pour l'affichage
        'type' => $type, // Passer le type à la vue
        'startDate' => $startDate, // Passer la date de début à la vue
        'endDate' => $endDate, // Passer la date de fin à la vue
    ]);
}
}

   



