<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\Appartement;
use Symfony\Component\Serializer\SerializerInterface;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Dompdf\Dompdf;

use Twilio\Rest\Client;
use App\Form\FactureType;
use Dompdf\Options;

use App\Repository\AppartementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FactureRepository;
use App\Service\PdfService;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\StatFilterType;

use Symfony\Component\HttpFoundation\JsonResponse;

class FactureController extends AbstractController
{
    #[Route('/facture', name: 'app_facture_index', methods: ['GET'])]
    public function index(Request $request, FactureRepository $factureRepository): Response
    {
        $sortBy = $request->query->get('sort_by', 'date'); // Tri par défaut par date
        $sortOrder = $request->query->get('sort_order', 'DESC');
        $search = $request->query->get('search', '');
        $type = $request->query->get('type', ''); // Nouveau paramètre pour le type de facture
    
        // Construire les critères de recherche
        $criteria = [];
        if (!empty($search)) {
            $criteria = [
                'type' => $search,

            ];
        }
        // Ajoutez le critère pour le type de facture
        if (!empty($type)) {
            $criteria['type'] = $type;
        }
    
        // Déterminez l'ordre de tri
        $orderBy = [];
        switch ($sortBy) {
            case 'date':
                $orderBy = ['date' => $sortOrder];
                break;
            // Ajoutez d'autres cas de tri si nécessaire
            default:
                // Défaut: tri par date
                $orderBy = ['date' => 'DESC'];
                break;
        }
    
        // Utilisez la méthode findBy avec les critères et l'ordre de tri
        $factures = $factureRepository->findBy($criteria, $orderBy);
    
        return $this->render('facture/index.html.twig', [
            'factures' => $factures,
        ]);
    }
    #[Route('/facture/new', name: 'app_facture_new', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $facture = new Facture();
        $form = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $facture->setPayee(false); 

            $entityManager->persist($facture);
            $entityManager->flush();
    
            $appartement = $facture->getIdAppartement();
            $nomResident = $appartement->getNomResident();
            $numTelResident = $appartement->getId()->getNumber();
    
            $numFacture = $facture->getNumFacture();
            $montantFacture = $facture->getMontant();
    
            // Initialize Twilio client with SID and token
                $sid = isset($_ENV['TWILIO_SID']) ? (string)$_ENV['TWILIO_SID'] : null;
                $token = isset($_ENV['TWILIO_TOKEN']) ? (string)$_ENV['TWILIO_TOKEN'] : null;

            // Check if the variables are set and not empty
            if ($sid && $token) {
                // Initialize Twilio client
                $twilio = new \Twilio\Rest\Client($sid, $token);
                
                $messageBody = "Cher(e) $nomResident, une nouvelle facture d'un montant de $montantFacture EUR a été ajoutée pour l'appartement $numFacture.";
    
                try {
                    $message = $twilio->messages->create(
                        "+216$numTelResident",
                        [
                            "from" => "+13347588346", // Your Twilio number
                            "body" => $messageBody
                        ]
                    );
    
                    if ($message->sid) {
                        $this->addFlash('success', 'Message envoyé avec succès.');
                    }
                } catch (\Twilio\Exceptions\RestException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'envoi du message: ' . $e->getMessage());
                }
            } else {
                // Handle the case when the Twilio SID or token is not set
                $this->addFlash('error', 'Les identifiants Twilio sont manquants.');
            }
    
            return $this->redirectToRoute('app_appartement_factures', ['idAppartement' => $appartement->getIdappartement()], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('facture/add.html.twig', [
            'facture' => $facture,
            'form' => $form->createView(),
        ]);
    }
    
#[Route('/facture/{idFacture}', name: 'app_facture_show', methods: ['GET'], requirements: ['idFacture' => '\d+'])]
public function show(int $idFacture, FactureRepository $factureRepository): Response
{
    $facture = $factureRepository->find($idFacture);

    if (!$facture) {
        throw $this->createNotFoundException('Facture non Trouvée');
    }

    // Retrieve the associated Appartement entity
    $appartement = $facture->getIdAppartement();

    return $this->render('facture/show.html.twig', [
        'facture' => $facture,
        'appartement' => $appartement,
    ]);
}



#[Route('/facture/{idFacture}/edit', name: 'app_facture_edit', methods: ['GET', 'POST'])]
public function edit(int $idFacture, Request $request, EntityManagerInterface $entityManager, FactureRepository $factureRepository): Response
{
    $facture = $factureRepository->find($idFacture);
    $form = $this->createForm(FactureType::class, $facture);
    $form->handleRequest($request);
    $appartement = $facture->getIdAppartement();


    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_facture_edit', ['idFacture' => $idFacture], Response::HTTP_SEE_OTHER);
    }

    return $this->render('facture/edit.html.twig', [
        'facture' => $facture,
        'form' => $form->createView(), 
        'appartement' => $appartement,

    ]);
}

#[Route('/facture/{idfacture}/delete', name: 'app_facture_delete')]
public function delete(int $idfacture, EntityManagerInterface $entityManager, FactureRepository $factureRepository): Response
{
    $facture = $factureRepository->find($idfacture);

    if (!$facture) {
        throw $this->createNotFoundException('Appartement not found');
    }

    $entityManager->remove($facture);
    $entityManager->flush();

    return $this->redirectToRoute('app_facture_index', [], Response::HTTP_SEE_OTHER);
}
#[Route('/facture/{idAppartement}/factures', name: 'app_appartement_factures')]
public function showAppartementFactures(int $idAppartement, FactureRepository $factureRepository, EntityManagerInterface $entityManager, Request $request): Response
{
    $appartement = $entityManager->getRepository(Appartement::class)->find($idAppartement); // Utilisez directement le gestionnaire d'entité
    if (!$appartement) {
        throw $this->createNotFoundException('Appartement non trouvé');
    }

    // Récupérer les paramètres de tri et de recherche depuis la requête
    $sortBy = $request->query->get('sort_by', 'date'); // Tri par défaut par date
    $sortOrder = $request->query->get('sort_order', 'DESC');
    $type = $request->query->get('type', ''); // Nouveau paramètre pour le type de facture

    // Construire les critères de recherche
    $criteria = ['idAppartement' => $appartement];
    if (!empty($type)) {
        $criteria['type'] = $type;
    }

    // Déterminez l'ordre de tri
    $orderBy = [];
    switch ($sortBy) {
        case 'date':
            $orderBy = ['date' => $sortOrder];
            break;
        // Ajoutez d'autres cas de tri si nécessaire
        default:
            // Défaut: tri par date
            $orderBy = ['date' => 'DESC'];
            break;
    }

    // Utilisez la méthode findBy avec les critères et l'ordre de tri
    $factures = $factureRepository->findBy($criteria, $orderBy);

    return $this->render('facture/facture.html.twig', [
        'appartement' => $appartement,
        'factures' => $factures,
        'sortBy' => $sortBy,
        'sortOrder' => $sortOrder, 
    ]);
}
#[Route('/appartement/{idAppartement}/facturesResiden', name: 'app_appartement_factures_Residen')]
public function showAppartementFacturesResident(int $idAppartement, FactureRepository $factureRepository, EntityManagerInterface $entityManager, Request $request): Response
{
    $appartement = $entityManager->getRepository(Appartement::class)->find($idAppartement); // Utilisez directement le gestionnaire d'entité
    if (!$appartement) {
        throw $this->createNotFoundException('Appartement non trouvé');
    }

    // Récupérer les paramètres de tri et de recherche depuis la requête
    $sortBy = $request->query->get('sort_by', 'date'); // Tri par défaut par date
    $sortOrder = $request->query->get('sort_order', 'DESC');
    $type = $request->query->get('type', ''); // Nouveau paramètre pour le type de facture

    // Construire les critères de recherche
    $criteria = ['idAppartement' => $appartement, 'payee' => false];
    if (!empty($type)) {
        $criteria['type'] = $type;
    }

    // Déterminez l'ordre de tri
    $orderBy = [];
    switch ($sortBy) {
        case 'date':
            $orderBy = ['date' => $sortOrder];
            break;
        // Ajoutez d'autres cas de tri si nécessaire
        default:
            // Défaut: tri par date
            $orderBy = ['date' => 'DESC'];
            break;
    }

    // Utilisez la méthode findBy avec les critères et l'ordre de tri
    $factures = $factureRepository->findBy($criteria, $orderBy);

    return $this->render('facture/factureResiden.html.twig', [
        'appartement' => $appartement,
        'factures' => $factures,
        'sortBy' => $sortBy,
        'sortOrder' => $sortOrder, 
    ]);
}

 
#[Route('/facture/{idFacture}/pdf', name: 'app_facture_pdf', methods: ['GET'])]
    public function generatePdf(int $idFacture, FactureRepository $factureRepository, PdfService $pdfService): Response
    {
        // Trouver la facture à partir de l'ID
        $facture = $factureRepository->find($idFacture);

        // Vérifier si la facture existe
        if (!$facture) {
            throw $this->createNotFoundException('Facture non trouvée');
        }

        $html = $this->renderView('facture/pdf_template.html.twig', [
            'facture' => $facture,
        ]);

        $pdfService->showPdfFile($html);

        return new Response();
    }
#[Route('/stats', name: 'stats')]
public function stats(Request $request, FactureRepository $factureRepository): Response
{
    // Récupérer le terme de recherche de la requête HTTP
    $term = $request->query->get('term');

    // Récupérer les factures en fonction du terme de recherche
    $factures = $factureRepository->createQueryBuilder('f')
        ->leftJoin('f.idAppartement', 'a')
        ->andWhere('a.numappartement LIKE :term')
        ->setParameter('term', '%' . $term . '%')
        ->getQuery()
        ->getResult();

    // Calculer la consommation totale et le montant total pour toutes les factures
    $consommationTotale = 0;
    $montantTotal = 0;

    foreach ($factures as $facture) {
        $consommationTotale += $facture->getConsommation();
        $montantTotal += $facture->getMontant();
    }

    // Rendre la vue avec les données
    return $this->render('facture/stats.html.twig', [
        'factures' => $factures,
        'consommationTotale' => $consommationTotale,
        'montantTotal' => $montantTotal,
    ]);
}

#[Route('/statistique', name: 'statistique')]
public function statistique(Request $request, FactureRepository $factureRepository): Response
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

    // Si le formulaire est soumis et valide, traiter les données
    if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();
        $type = $data['type'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Récupérer les factures filtrées en fonction du type et de la plage de dates
        $factures = $factureRepository->findByTypeAndDateRange($type, $startDate, $endDate);
        dump($factures);

        // Calculer les statistiques à partir des factures filtrées
        foreach ($factures as $facture) {
            $consommationTotale += $facture->getConsommation();
            $montantTotal += $facture->getMontant();
        }
    }

    // Rendre la vue avec les données de statistiques et les factures filtrées
    return $this->render('facture/stats_filter.html.twig', [
        'factures' => $factures,
        'consommationTotale' => $consommationTotale,
        'montantTotal' => $montantTotal,
        'form' => $form->createView(), // Passer le formulaire à la vue pour l'affichage
        'type' => $type, // Passer le type à la vue
        'startDate' => $startDate, // Passer la date de début à la vue
        'endDate' => $endDate, // Passer la date de fin à la vue
    ]);
}
#[Route('/facture/{idFacture}/paiement', name: 'app_facture_paiement', methods: ['GET', 'POST'])]
public function paiement(int $idFacture, FactureRepository $factureRepository, Request $request, EntityManagerInterface $entityManager): Response
{
    // Récupérer la facture à partir de l'ID
    $facture = $factureRepository->find($idFacture);

    // Vérifier si la facture existe
    if (!$facture) {
        throw $this->createNotFoundException('Facture non trouvée');
    }

    // Configurer Stripe avec votre clé secrète
    Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

    // Créer un paiement Stripe
    $paymentIntent = PaymentIntent::create([
        'amount' => $facture->getMontant() * 100, // Le montant doit être en cents
        'currency' => 'eur',
        'description' => 'Paiement de facture',
    ]);

    if ($request->isMethod('POST')) {
     
        $facture->setPayee(true); 
        $entityManager->flush();

        return $this->redirectToRoute('confirmation_paiement');
    }

    $stripePublicKey = $_ENV['STRIPE_PUBLIC_KEY'];

    return $this->render('facture/paiement.html.twig', [
        'facture' => $facture,
        'clientSecret' => $paymentIntent->client_secret,
        'stripe_public_key' => $stripePublicKey, 
        'paymentIntent' => $paymentIntent, 
    ]);
}
#[Route('/facture/{idFacture}/marquer-payee', name: 'app_facture_marquer_payee', methods: ['POST'])]
public function marquerPayee(int $idFacture, FactureRepository $factureRepository, EntityManagerInterface $entityManager): JsonResponse
{
    // Récupérer la facture à partir de l'ID
    $facture = $factureRepository->find($idFacture);

    // Vérifier si la facture existe
    if (!$facture) {
        return new JsonResponse(['message' => 'Facture non trouvée'], JsonResponse::HTTP_NOT_FOUND);
    }

    // Marquer la facture comme payée
    $facture->setPayee(true);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Facture marquée comme payée'], JsonResponse::HTTP_OK);
}
#[Route('/facture/{idFacture}/telecharger-recu-pdf', name: 'download_receipt_pdf')]
public function downloadReceiptPdf(int $idFacture, FactureRepository $factureRepository, PdfService $pdfService): Response
{
    // Récupérer la facture à partir de l'ID
    $facture = $factureRepository->find($idFacture);

    // Vérifier si la facture existe
    if (!$facture) {
        throw $this->createNotFoundException('Facture non trouvée');
    }

    // Générer le contenu PDF pour la facture
    $pdfContent = $pdfService->generateInvoicePDF($facture);

    // Retourner le PDF en réponse HTTP
    return new Response($pdfContent, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="Recu_paiement.pdf"',
    ]);
}

#[Route('/appartement/{idAppartement}/facturesResiden/payees', name: 'app_appartement_factures_payees')]
public function showAppartementFacturesPayees(int $idAppartement, FactureRepository $factureRepository, EntityManagerInterface $entityManager, Request $request): Response
{
    $appartement = $entityManager->getRepository(Appartement::class)->find($idAppartement);
    if (!$appartement) {
        throw $this->createNotFoundException('Appartement non trouvé');
    }

    // Construire les critères de recherche pour récupérer uniquement les factures payées
    $criteria = ['idAppartement' => $appartement, 'payee' => true];

    // Récupérer les paramètres de tri et de recherche depuis la requête
    $sortBy = $request->query->get('sort_by', 'date');
    $sortOrder = $request->query->get('sort_order', 'DESC');
    $type = $request->query->get('type', '');

    // Ajouter des critères supplémentaires si nécessaire
    if (!empty($type)) {
        $criteria['type'] = $type;
    }

    // Déterminez l'ordre de tri
    $orderBy = [];
    switch ($sortBy) {
        case 'date':
            $orderBy = ['date' => $sortOrder];
            break;
        // Ajoutez d'autres cas de tri si nécessaire
        default:
            // Défaut: tri par date
            $orderBy = ['date' => 'DESC'];
            break;
    }

    // Utilisez la méthode findBy avec les critères et l'ordre de tri
    $factures = $factureRepository->findBy($criteria, $orderBy);

    return $this->render('facture/archive.html.twig', [
        'appartement' => $appartement,
        'factures' => $factures,
        'sortBy' => $sortBy,
        'sortOrder' => $sortOrder, 
    ]);
}


}







