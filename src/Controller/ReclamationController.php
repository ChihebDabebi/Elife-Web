<?php

namespace App\Controller;
use App\Controller\MailerOuController;
use App\Entity\Reclamation;
use App\Form\ReclamationType;

use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Gregwar\Captcha\CaptchaBuilder;
use Symfony\Component\Form\FormError;


#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET', 'POST'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        // Get the list of categories
        $categories = $reclamationRepository->findDistinctCategories();
        
        // Initialize the category filter
        $selectedCategory = null;
        $filteredReclamations = [];
        $statistics = []; // Initialize statistics array

        // Handle form submission
        $form = $this->createFormBuilder()
            ->add('category', ChoiceType::class, [
                'choices' => array_combine($categories, $categories),
                'placeholder' => 'Select a category',
                'required' => false,
            ])
            ->add('displayOption', ChoiceType::class, [
                'choices' => [
                    'Statistics by category' => 'category',
                    'Statistics by date' => 'date',
                ],
                'placeholder' => 'Select display option',
                'required' => true,
            ])
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $selectedCategory = $form->get('category')->getData();
            $displayOption = $form->get('displayOption')->getData();
            if ($selectedCategory) {
                if ($displayOption === 'category') {
                    $filteredReclamations = $reclamationRepository->findBy(['categorierec' => $selectedCategory]);
                    $statistics = $reclamationRepository->getReclamationsByCategory();
                } elseif ($displayOption === 'date') {
                    // Fetch reclamations by daterec
                    $filteredReclamations = $reclamationRepository->findAll(); // Change this to fetch reclamations by daterec
                    $statistics = $reclamationRepository->getReclamationsByDaterec();
                }
            }
        } else {
            // If form is not submitted or no category is selected, show all reclamations
            $filteredReclamations = $reclamationRepository->findAll();
        }

        return $this->render('reclamation/index.html.twig', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'reclamations' => $filteredReclamations,
            'statistics' => $statistics,
            'form' => $form->createView(),
        ]);
    }
    
    


    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $reclamation = new Reclamation();
    $form = $this->createForm(ReclamationType::class, $reclamation);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle image data
        $imageFile = $form->get('imagedata')->getData();
        if ($imageFile) {
            // Store the image data
            $imageData = file_get_contents($imageFile);
            $reclamation->setImagedata($imageData);
        }

        // Set date to current date
        $reclamation->setDaterec(new \DateTime());

        // Set user id to currently logged in user
        $user = $this->getUser();
        $reclamation->setIdu($user);

        $entityManager->persist($reclamation);
        $entityManager->flush();
// Call the sendMail() action of MailerController to send email
$this->forward(MailerOuController::class . '::sendMail');
        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('reclamation/new.html.twig', [
        'reclamation' => $reclamation,
        'form' => $form->createView(),
    ]);
}





    #[Route('/{idrec}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{idrec}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image data
            $imageFile = $form->get('imagedata')->getData();
            if ($imageFile) {
                // Store the new image data
                $imageData = file_get_contents($imageFile);
                $reclamation->setImagedata($imageData);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{idrec}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getIdrec(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('', name: 'search_reclamation')]
    public function search(Request $request, ReclamationRepository $ReclamationsRepository): Response
    {
        $query = $request->query->get('query');
        $reclamations = $ReclamationsRepository->findBy(['descrirec' => $query]);
        return $this->render('reclamation/search.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }
} 