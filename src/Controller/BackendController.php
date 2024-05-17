<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\EditUtilisateurType;
use App\Form\EditProfileFormType;

use App\Form\UpdateFormType;
use App\Form\UserType;
use App\Form\UtilisateurAuthorType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BackendController extends AbstractController
{

    #[Route('/backendHome', name: 'backendHome')]
    public function backendHome(UserRepository $utilisateurRepository): Response
    {
        $user=$utilisateurRepository->findAll();
        return $this->render('backend/UsersList.html.twig', [
            'table' => $user
        ]);
    }

    #Backend CRUD

    #[Route('/displayUsers', name: 'displayUsers')]
    public function displayUsers(UserRepository $utilisateurRepository): Response
    {
        $user=$utilisateurRepository->findAll();
        return $this->render('backend/UsersList.html.twig', [
            'table' => $user
    
            
        ]);
    }

    #[Route('/addUser', name: 'addUser')]
    public function addUser(ManagerRegistry $managerRegistry, Request $request): Response
    {
        $m=$managerRegistry->getManager();
        $user=new User();
        $form=$this->createForm(UtilisateurAuthorType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            
            $m->persist($user);
            $m->flush();

            return $this->redirectToRoute('displayUsers');
        }
        return $this->render('backend/addUser.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/editUser/{id}', name: 'editUser')]
    public function editUser(UserRepository $utilisateurRepository, ManagerRegistry $managerRegistry, Request $request,$id): Response
    {
        $m=$managerRegistry->getManager();
        $findid=$utilisateurRepository->find($id);
        $form=$this->createForm(EditProfileFormType::class,$findid);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $m->persist($findid);
            $m->flush();

            return $this->redirectToRoute('displayUsers');
        }
        return $this->render('backend/edituserbyid.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/deleteUser/{id}', name: 'deleteUser')]
    public function deleteUser(UserRepository $utilisateurRepository, ManagerRegistry $managerRegistry, $id): Response
    {
        $m=$managerRegistry->getManager();
        $findid=$utilisateurRepository->find($id);
        $m->remove($findid);
        $m->flush();
        return $this->redirectToRoute('displayUsers');
    }

    # Profile business

    #[Route('/adminProfile', name: 'adminProfile')]
    public function adminProfile(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        
        return $this->render('backend/adminprofile.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'BackendController',
        ]);
    }
    #[Route('/editAdmin', name: 'editAdmin')]
    public function editAdmin(UserRepository $userRepository, ManagerRegistry $managerRegistry, Request $request): Response
    {
        $m=$managerRegistry->getManager();
        $findid=$this->getUser();
        $form=$this->createForm(EditUtilisateurType::class,$findid);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $m->persist($findid);
            $m->flush();

            return $this->redirectToRoute('adminProfile');
        }
        return $this->render('backend/editprofile.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('', name: 'search_users')]
    public function search(Request $request, UserRepository $utilisateurRepository): Response
    {
        $query = $request->query->get('query');
        $users = $utilisateurRepository->findBy(['nom' => $query]);
        return $this->render('backend/search.html.twig', [
            'users' => $users,
        ]);
    }
   // $user=$utilisateurRepository->findAll();
    //return $this->render('backend/UsersList.html.twig', [
      //  'table' => $user
//]);
}