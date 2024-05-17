<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

use App\Entity\Parking;
use App\Entity\Voiture;
use App\Form\VoitureType;
use App\Form\VoitureTypeNew;
use chillerlan\QRCode\QRCode;
use App\Services\EmailSender; 
use Symfony\Component\Mime\Email;
use App\Repository\ParkingRepository;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class VoitureController extends AbstractController
{
    #[Route('/voitures', name: 'app_voitures')]
    public function index(Request $request, VoitureRepository $voitureRepository, PaginatorInterface $paginator): Response
    {
        // Récupérez le terme de recherche à partir de la requête
        $searchTerm = $request->query->get('search');
        // Récupérez les paramètres de tri à partir de la requête
        $sortBy = $request->query->get('sort_by', 'marque');
        $sortOrder = $request->query->get('sort_order', 'ASC');
    
        // Construisez la requête
        $queryBuilder = $voitureRepository->createQueryBuilder('v');
        // Filtrez les voitures en fonction du terme de recherche
        if ($searchTerm) {
            $queryBuilder->andWhere('v.marque LIKE :searchTerm')
                         ->orWhere('v.model LIKE :searchTerm')
                         ->setParameter('searchTerm', '%'.$searchTerm.'%');
        }
    
        // Construisez le critère de tri
        if ($sortBy && $sortOrder) {
            $queryBuilder->orderBy('v.'.$sortBy, $sortOrder);
        }
    
        // Exécutez la requête
        $query = $queryBuilder->getQuery();
    
        // Paginez les résultats
        $voitures = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // Get page number from the request, default to 1
            3 // Number of items per page
        );
    
        // Passez searchTerm et les paramètres de tri à la vue Twig
        return $this->render('voiture/index.html.twig', [
            'voitures' => $voitures,
            'searchTerm' => $searchTerm,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/voitures/search', name: 'app_voitures_search')]
    public function search(Request $request): Response
    {
        // Récupérez le terme de recherche à partir de la requête
        $searchTerm = $request->query->get('search');
        // Redirigez vers la route de liste des voitures avec le terme de recherche
        return $this->redirectToRoute('app_voitures', ['search' => $searchTerm]);
    }

    #[Route('/voitures/sort', name: 'app_voitures_sort')]
    public function sort(Request $request): Response
    {
        // Récupérez les paramètres de tri à partir de la requête
        $sortBy = $request->query->get('sort_by');
        $sortOrder = $request->query->get('sort_order');
        // Redirigez vers la route de liste des voitures avec les paramètres de tri
        return $this->redirectToRoute('app_voitures', ['sort_by' => $sortBy, 'sort_order' => $sortOrder]);
    }

    
    
    
    

    #[Route('/voiture/new', name: 'app_voiture_new', methods: ['GET', 'POST'])]
    public function new(Request $request,ManagerRegistry $doctrine, EntityManagerInterface $entityManager, VoitureRepository $voitureRepository, ParkingRepository $parkingRepository, EmailSender $emailSender): Response
    {
        $voiture = new Voiture();
        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la réponse du reCAPTCHA soumise par l'utilisateur
            $recaptchaResponse = $request->request->get('g-recaptcha-response');

            // Vérifier si la réponse du reCAPTCHA est vide
            if (!$recaptchaResponse) {
                // Si la réponse du reCAPTCHA est vide, rediriger avec un message d'erreur
                $this->addFlash('captcha_error', 'Le reCAPTCHA est invalide. Veuillez réessayer.');

                return $this->redirectToRoute('app_voiture_new');
            }

            // Effectuer une requête vers l'API reCAPTCHA pour vérifier la réponse
            $client = HttpClient::create();
            $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => '6Le5wsUpAAAAAIVIKnHzSSVk_kEpMWK7PtAecw7P',
                    'response' => $recaptchaResponse,
                ],
            ]);

            // Récupérer la réponse de l'API reCAPTCHA
            $responseData = $response->toArray();

            // Vérifier si la réponse de l'API indique que le reCAPTCHA est valide
            if (!$responseData['success']) {
                // Si le reCAPTCHA est invalide, rediriger avec un message d'erreur
                $this->addFlash('error', 'Le reCAPTCHA est invalide. Veuillez réessayer.');
                return $this->redirectToRoute('app_voiture_new');
            }

            // Vérifier si le numéro de matricule existe déjà
            $existingVoiture = $voitureRepository->findOneBy(['matricule' => $voiture->getMatricule()]);
            if ($existingVoiture) {
                // Si un enregistrement avec le même numéro de matricule existe déjà, afficher un message d'erreur
                $this->addFlash('error', 'Cette matricule existe déjà.');
                return $this->redirectToRoute('app_voiture_new');
            }

            // Récupérer le parking sélectionné pour la nouvelle voiture
        $selectedParking = $voiture->getidParking();
        // Vérifier si le parking sélectionné a des places disponibles
        if ($selectedParking && $selectedParking->getNombreActuelles() >= $selectedParking->getCapacite()) {
            // Si le parking est plein, afficher un message d'erreur
            $this->addFlash('error', 'Le parking sélectionné est plein. Veuillez choisir un autre parking.');
            return $this->redirectToRoute('app_voiture_new');
        }
        $userRepository = $doctrine->getRepository(User::class);
        $userId = $request->getSession()->get('user_id',null);

        $connectedUser = $userRepository->find($userId);
        $voitures = $this->getDoctrine()->getRepository(Voiture::class)->findVoituresByUserId($userId);
            $voiture->setId($connectedUser);
            $entityManager->persist($voiture);
            $entityManager->flush();

            // Incrémenter le nombre de places actuelles du parking sélectionné
        $selectedParking->setNombreActuelles($selectedParking->getNombreActuelles() + 1);
        $entityManager->flush();

            // Créer une date universelle
             $dateUniverselle = new DateTime('now', new DateTimeZone('UTC'));
             $dateUniverselle->setTimezone(new DateTimeZone('Europe/Paris')); // Changer le fuseau horaire selon votre besoin

             // Envoyer un e-mail 
             $emailMessage = 'Votre voiture a été ajoutée avec succès le ' . $dateUniverselle->format('Y-m-d H:i:s') . '.';
             $emailSender->sendEmail('ademzitouni05@gmail.com', 'Nouvelle voiture ajoutée', $emailMessage);
             // Rediriger vers la page de création de voiture avec un message de succès
             $this->addFlash('success', 'La voiture a été ajoutée avec succès.');
             return $this->redirectToRoute('app_voiture_qrcode', ['idVoiture' => $voiture->getIdvoiture()]);
            }
             // Récupérer les informations sur les parkings
             $parkings = $parkingRepository->findAll();


             // Calculer le nombre de places disponibles pour chaque parking
                  $availablePlaces = [];
                  foreach ($parkings as $parking) {
             // Calculer le nombre de places disponibles en soustrayant le nombre actuel de voitures stationnées de la capacité totale du parking
                  $placesDisponibles = $parking->getCapacite() - $parking->getNombreActuelles();
                  $availablePlaces[$parking->getIdparking()] = $placesDisponibles;
    }

            // Récupérer tous les parkings
                  $parkings = $parkingRepository->findAll();
                  return $this->renderForm('voiture/new.html.twig', [
                  'voiture' => $voiture,
                  'form' => $form,
                  'recaptcha_site_key' => '6Le5wsUpAAAAABwJZOgST880D7Jk0NfhYgt_PdKw',
                  'availablePlaces' => $availablePlaces,
                  'parkings' => $parkings, 

        ]);
    }



    #[Route('/voiture/{idVoiture}/qrcode', name: 'app_voiture_qrcode')]
    public function generateQrCode(int $idVoiture, VoitureRepository $voitureRepository, UrlGeneratorInterface $urlGenerator): Response
    {
        $voiture = $voitureRepository->find($idVoiture);

        if (!$voiture) {
            throw $this->createNotFoundException('Voiture not found');
        }

        // Générer l'URL pour l'action qui va générer l'image du QR code
        $qrCodeImageUrl = $urlGenerator->generate('app_voiture_qrcode_image', ['idVoiture' => $idVoiture]);

        // Générer le contenu du QR code avec la matricule de la voiture
        $qrCodeContent = $voiture->getMatricule();
        
        // Créer l'image du QR code
        $qrCode = new QRCode;
        $qrCodeImage = $qrCode->render($qrCodeContent);
        
        // Renvoyer la vue Twig avec l'URL de l'image du code QR
        return $this->render('voiture/qrcode.html.twig', [
            'qrCodeImageUrl' => $qrCodeImageUrl,
            'qrCodeImage' => $qrCodeImage, 

        ]);
    }

    #[Route('/voiture/{idVoiture}/qrcode/image', name: 'app_voiture_qrcode_image')]
    public function generateQrCodeImage(int $idVoiture, VoitureRepository $voitureRepository): Response
    {
        // Trouver la voiture correspondante en utilisant l'id
        $voiture = $voitureRepository->find($idVoiture);

        // Vérifier si la voiture existe
        if (!$voiture) {
            throw $this->createNotFoundException('Voiture not found');
        }

        // Générer le contenu du QR code avec la matricule de la voiture
        $qrCodeContent = $voiture->getMatricule();
        
        // Créer l'image du QR code
        $qrCode = new QRCode;
        $qrCodeImageContent = $qrCode->render($qrCodeContent);
        
        // Renvoyer l'image générée avec le bon type MIME
        return new Response($qrCodeImageContent, Response::HTTP_OK, ['Content-Type' => 'image/png']);
    }
    
    #[Route('/voiture/{idVoiture}', name: 'app_voiture_show', methods: ['GET'])]
    public function show(int $idVoiture, VoitureRepository $voitureRepository): Response
    {
        $voiture = $voitureRepository->find($idVoiture);

        if (!$voiture) {
            throw $this->createNotFoundException('Voiture not found');
        }

        return $this->render('voiture/show.html.twig', [
            'voiture' => $voiture,
        ]);
    }

    
    
    #[Route('/voiture/{idVoiture}/delete', name: 'app_voiture_delete')]
public function delete(int $idVoiture, EntityManagerInterface $entityManager, VoitureRepository $voitureRepository): Response
{
    $voiture = $voitureRepository->find($idVoiture);

    if (!$voiture) {
        throw $this->createNotFoundException('Voiture not found');
    }
    $selectedParking = $voiture->getidParking();
    $entityManager->remove($voiture);
    $selectedParking->setNombreActuelles($selectedParking->getNombreActuelles() - 1);
    $entityManager->flush();

    return $this->redirectToRoute('app_voitures', [], Response::HTTP_SEE_OTHER);
}

#[Route('/voiture/{idVoiture}/edit', name: 'app_voiture_edit', methods: ['GET', 'POST'])]
public function edit(int $idVoiture, Request $request, EntityManagerInterface $entityManager, VoitureRepository $voitureRepository): Response
{

    $voiture = $voitureRepository->find($idVoiture);
    if (!$voiture) {
        throw $this->createNotFoundException('Voiture not found');
    }

    $form = $this->createForm(VoitureTypeNew::class, $voiture);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Le formulaire a été soumis et est valide, donc mettez à jour l'entité dans la base de données
        $entityManager->flush();

        return $this->redirectToRoute('app_voitures', [], Response::HTTP_SEE_OTHER);
    }

    // Le formulaire n'a pas été soumis ou n'est pas valide, affichez à nouveau le formulaire d'édition
    return $this->renderForm('voiture/edit.html.twig', [
        'voiture' => $voiture,
        'form' => $form,
    ]);
}

#[Route('/voitures/list', name: 'app_voitures_list')]
public function list(Request $request,VoitureRepository $voitureRepository,ManagerRegistry $doctrine): Response
{   
    $userRepository = $doctrine->getRepository(User::class);
        $userId = $request->getSession()->get('user_id',null);

        $connectedUser = $userRepository->find($userId);
        $voitures = $this->getDoctrine()->getRepository(Voiture::class)->findVoituresByUserId($userId);

    return $this->render('voiture/list.html.twig', [
        'voitures' => $voitures,
    ]);
}

#[Route('/voiture/{idVoiture}/edit-voiture', name: 'app_voiture_edit_voiture', methods: ['GET', 'POST'])]
public function editVoiture(int $idVoiture, Request $request, EntityManagerInterface $entityManager, VoitureRepository $voitureRepository): Response
{
    $voiture = $voitureRepository->find($idVoiture);
    if (!$voiture) {
        throw $this->createNotFoundException('Voiture not found');
    }

    $form = $this->createForm(VoitureType::class, $voiture);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Effectuez les actions nécessaires, par exemple, mettez à jour l'entité dans la base de données
        $entityManager->flush();

        // Redirigez vers la page appropriée après l'action réussie
        return $this->redirectToRoute('app_voitures_list', [], Response::HTTP_SEE_OTHER);
    }

    // Si le formulaire n'est pas soumis ou n'est pas valide, affichez à nouveau le formulaire
    return $this->render('voiture/edit_voiture.html.twig', [
        'voiture' => $voiture,
        'form' => $form->createView(), // Utilisez createView() pour obtenir un objet FormView
    ]);
}

#[Route('/voiture/{idVoiture}/deletevoiture', name: 'app_voiture_deletevoiture')]
public function deleteVoiture(int $idVoiture, EntityManagerInterface $entityManager, VoitureRepository $voitureRepository): Response
{
    $voiture = $voitureRepository->find($idVoiture);

    if (!$voiture) {
        throw $this->createNotFoundException('Voiture not found');
    }
    $selectedParking = $voiture->getidParking();
    $entityManager->remove($voiture);
    $selectedParking->setNombreActuelles($selectedParking->getNombreActuelles() - 1);

    $entityManager->flush();

    // Utilisez la route 'list' pour la redirection vers la liste des voitures dans la partie front-end
    return $this->redirectToRoute('app_voitures_list', [], Response::HTTP_SEE_OTHER);
}




}