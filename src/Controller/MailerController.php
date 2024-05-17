<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\Transport;

#[Route('/mailer', name: 'app_mailer_')]
class MailerController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('mailer/index.html.twig', [
            'controller_name' => 'MailerController',
        ]);
    }

    #[Route('/sendMail', name: 'sendMail')]
    public function sendMail(): Response
    {
        $transport = Transport::fromDsn('smtp://salhiomar362@gmail.com:pnoavpopklfyybeb@smtp.gmail.com:587');
        $mailer=new Mailer($transport);


        $email = (new TemplatedEmail())
            ->from('salhiomar362@gmail.com')
            ->to('koussay14.09.r@gmail.com')
            ->subject('Hello from Symfony Mailer')
            ->html('<a href="https://www.youtube.com/"> youtube </a>');
           

        $mailer->send($email);
        return new Response('Mail sent!');
    }
}
