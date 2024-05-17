<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\UserRepository;

class SecurityController extends AbstractController
{
    #[Route('/backend', name: 'app_back')]
    public function index2(Request $request, UserRepository $userRepository): Response
    {
        $userId = $request->getSession()->get('user_id',null);
        $connectedUser = $userRepository->find($userId); 

        return $this->render('base.html.twig', [
            'controller_name' => 'DiscussionController',
            'id'=>$userId,
            'connectedUser'=>$connectedUser
        ]);
    }
    private function generateTextCaptcha(): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $captchaText = '';
        for ($i = 0; $i < 6; $i++) {
            $captchaText.= $characters[rand(0, strlen($characters) - 1)];
        }
        return $captchaText;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
       // if ($this->getUser()) {
        //    return $this->redirectToRoute('app_admin');
        // }

        // Generate the text-based CAPTCHA
        $captchaText = $this->generateTextCaptcha();

        // Store the CAPTCHA text in the session
        $session = $request->getSession();
        $session->set('captcha', $captchaText);

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'captcha' => $captchaText,
        ]);
    }

    #[Route(path: '/login_check', name: 'app_login_check')]
    public function loginCheck(Request $request): void
    {
        // Get the CAPTCHA text from the session
        $session = $request->getSession();
        $captchaText = $session->get('captcha');

        // Get the CAPTCHA text from the request
        $captchaRequest = $request->request->get('captcha');

        // Verify the CAPTCHA text
        if ($captchaText!== $captchaRequest) {
            throw new \Exception('Invalid CAPTCHA');
        }

        // Proceed with the authentication process
        //...
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}