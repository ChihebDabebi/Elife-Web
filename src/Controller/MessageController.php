<?php

namespace App\Controller;
use App\Entity\Discussion;
use App\Entity\Message;
use App\Entity\User;

use Doctrine\Persistence\ManagerRegistry;
use App\Repository\DiscussionRepository;
use App\Repository\MessageRepository;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(): Response
    {
        return $this->render('base.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }

    // src/Controller/DiscussionController.php
#[Route('/discussion/{id}', name: 'add_message', methods: ['GET', 'POST'])]
public function show(int $id,DiscussionRepository $discussionRepository, MessageRepository $messageRepository,Request $request,ManagerRegistry $doctrine)
{   
    $discussion = $discussionRepository->find($id);
    $colorCode = $discussion->getColorCode();
    $messages = $messageRepository->findMessagesByDiscussion($id);
    foreach ($messages as $message) {
        $imageData = $message->getImage(); // Assuming getImage() returns the image data
        if ($imageData) {
            // Check if $imageData is a resource
            if (is_resource($imageData)) {
                // Convert resource to string
                $imageData = stream_get_contents($imageData);
            }
            // Encode binary image data to base64
            $encodedImage = base64_encode($imageData);
            $message->setImage($encodedImage);
        }
    }
    $userRepository = $doctrine->getRepository(User::class);
    $userId = $request->getSession()->get('user_id',null);

        $connectedUser = $userRepository->find($userId); 
        if (!$connectedUser) {
            throw $this->createNotFoundException('No user found for id :'. $userId);
        }
        $em = $doctrine->getManager();
        $message = new Message();
        $message->setEmetteur($connectedUser);
        $message->setDiscussion($discussion);
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //read bytes from the image 

            $uploadedFile = $form->get('image')->getData();
            if($uploadedFile){
            $message->setImage(file_get_contents($uploadedFile->getPathname()));
        }
            $em->persist($message); // the image will be stored as blob
            $em->flush();

            return $this->redirectToRoute('add_message', ['id' => $id]);
        }


    return $this->renderForm('message/addMessage.html.twig', [
        'id'=>$id,
        'messages' => $messages,
        'message' => $message,
        'form' => $form,
        'connectedUser'=>$connectedUser,
        'colorCode'=>$colorCode,
        'discussion'=>$discussion,
    ]);
}
#[Route('/message/delete/{id}/{idDiscussion}', name: 'delete_message')]
    public function delete(int $id,FlashyNotifier $flashy, int $idDiscussion,  EntityManagerInterface $entityManager, MessageRepository $messageRepository): Response
    {
        $message = $messageRepository->find($id);
    
        if (!$message) {
            throw $this->createNotFoundException('Message not found');
        }
    
        $entityManager->remove($message);
        $flashy->info('Discussion  deleted successfully!', '#');

        $entityManager->flush();
    
        return $this->redirectToRoute('add_message',  ['id' => $idDiscussion], Response::HTTP_SEE_OTHER);
    }
    #[Route('/message/update/{id}/{idDiscussion}', name: 'update_message')]

    public function updateMessage(Request $request,DiscussionRepository $discussionRepository, $id, int $idDiscussion,  EntityManagerInterface $entityManager, MessageRepository $messageRepository,ManagerRegistry $doctrine)
{
    $message = $messageRepository->find($id);
    $discussion = $discussionRepository->find($idDiscussion);
    $colorCode = $discussion->getColorCode();
    
    $messages = $messageRepository->findMessagesByDiscussion($id);
    $userRepository = $doctrine->getRepository(User::class);
    $userId = $request->getSession()->get('user_id',null);

    $connectedUser = $userRepository->find($userId); 
    
    $form = $this->createForm(MessageType::class, $message);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($message);
        $entityManager->flush();
        
        return $this->redirectToRoute('add_message',  ['id' => $idDiscussion], Response::HTTP_SEE_OTHER);
    }
    
    return $this->render('message/addMessage.html.twig', [
        'id'=>$idDiscussion,
        'messages' => $messages,
        'form' => $form->createView(),
        'message' => $message,
        'connectedUser'=>$connectedUser,
        'colorCode'=>$colorCode
    ]);
}


}