<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Discussion;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

use App\Form\DiscussionType;
use App\Repository\DiscussionRepository;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DiscussionController extends AbstractController
{
  
    #[Route('/discussion', name: 'app_discussion')]
    public function index(): Response
    {
        return $this->render('discussion/index.html.twig', [
            'controller_name' => 'DiscussionController',
        ]);
    }
    #[Route('/discussions', name: 'list_discussions', methods: ['GET'])]
    public function show(Request $request,DiscussionRepository $discussionRepository,ManagerRegistry $doctrine,PaginatorInterface $paginator):Response{
        $discussions = $discussionRepository->findAll();
        $userRepository = $doctrine->getRepository(User::class);
        $userId = $request->getSession()->get('user_id',null);

        $connectedUser = $userRepository->find($userId); 
        $discussions = $paginator->paginate(
            $discussions, /* query NOT result */
            $request->query->getInt('page', 1),
            4
        );
        return $this->render('discussion/listDiscussion.html.twig', [
            'discussions' => $discussions,
            'connectedUser' => $connectedUser
        ]);
        

    }
    #[Route('/discussion/add', name: 'add_discussion', methods: ['GET', 'POST'])]
    public function new(Request $request,ManagerRegistry $doctrine,FlashyNotifier $flashy): Response
    {
        $userRepository = $doctrine->getRepository(User::class);
        $userId = $request->getSession()->get('user_id',null);

        $connectedUser = $userRepository->find($userId); 
        if (!$connectedUser) {
            throw $this->createNotFoundException('No user found for id 1');
        }
        $em = $doctrine->getManager();
        $discussion = new Discussion();
        $discussion->setCreateur($connectedUser);
        $form = $this->createForm(DiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($discussion);
            $flashy->success('Discussion  created successfully!', '#');

            $em->flush();

            return $this->redirectToRoute('list_discussions');
        }

        return $this->renderForm('/discussion/add.html.twig', [
            'discussion' => $discussion,
            'form' => $form,
            'connectedUser'=> $connectedUser
        ]);
    }
    #[Route('/discussion/edit/{id}', name: 'edit_discussion', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request,ManagerRegistry $doctrine, EntityManagerInterface $entityManager,FlashyNotifier $flashy,DiscussionRepository $discussionRepository): Response
    {
        $discussion = $discussionRepository->find($id);
        $userId = $request->getSession()->get('user_id',null);
        $userRepository = $doctrine->getRepository(User::class);

        $connectedUser = $userRepository->find($userId); 
        $form = $this->createForm(DiscussionType::class, $discussion);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $flashy->success('Discussion  updated successfully!', '#');

            $entityManager->flush();
    
            return $this->redirectToRoute('list_discussions', ['id' => $id], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('discussion/modifier.html.twig', [
            'discussion' => $discussion,
            'form' => $form->createView(),
            'connectedUser'=> $connectedUser
        ]);
    }
    #[Route('/discussion/delete/{idDiscussion}', name: 'delete_discussion')]
    public function delete(int $idDiscussion,FlashyNotifier $flashy, EntityManagerInterface $entityManager, DiscussionRepository $discussionRepository): Response
    {
        $discussion = $discussionRepository->find($idDiscussion);
    
        if (!$discussion) {
            throw $this->createNotFoundException('Discussion not found');
        }
    
        $entityManager->remove($discussion);
        $flashy->info('Discussion  deleted successfully!', '#');
        $entityManager->flush();
    
        return $this->redirectToRoute('list_discussions', [], Response::HTTP_SEE_OTHER);
    }



}