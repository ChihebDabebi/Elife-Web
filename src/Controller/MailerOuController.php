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
class MailerOuController extends AbstractController
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
            ->to('oussema.chebi@gmail.com')
            ->subject('Reclamation submission')
            ->html('<p>A new reclamation has been submitted.Its being treated and we will respond as soon as possible.</p><p>You can view the reclamation <a href="http://127.0.0.1:8000/reclamation/">here</a>.</p>');
           

        $mailer->send($email);
        return new Response('Mail sent!');
    }
}