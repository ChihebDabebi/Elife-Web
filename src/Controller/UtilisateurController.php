<?php

namespace App\Controller;
use App\Form\EditUtilisateurType;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;


class UtilisateurController extends AbstractController
{       #[Route('/utilisateurHome', name: 'utilisateurHome')]
    public function utilisateurHome(): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
    #[Route('/utilisateurLogIn', name: 'utilisateurLogIn')]
    public function utilisateurLohgIn(): Response
    {

        return $this->render('utilisateur/login.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
    #[Route('/adminHome', name: 'adminHome')]
    public function adminHome(UserRepository $utilisateurRepository): Response
    {    $user=$utilisateurRepository->findAll();
        return $this->render('backend/UsersList.html.twig', [
            'controller_name' => 'UtilisateurController',
            'table' => $user
        ]);
    }
    #[Route('/Profile', name: 'Profile')]
    public function Profile(UserRepository $utilisateurRepository): Response
    {
        $user=$utilisateurRepository->findAll();
        return $this->render('afterlogin/profile.html.twig', [
            'table' => $user,
        ]);
    }
    #[Route('/editProfile', name: 'editProfile')]
    public function editProfile(UserRepository $userRepository, ManagerRegistry $managerRegistry, Request $request): Response
    {
        $m=$managerRegistry->getManager();
        $findid=$this->getUser();
        $form=$this->createForm(EditUtilisateurType::class,$findid);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $m->persist($findid);
            $m->flush();

            return $this->redirectToRoute('Profile');
        }
        return $this->render('afterlogin/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}