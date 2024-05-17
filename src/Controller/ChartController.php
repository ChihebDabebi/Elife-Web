<?php

namespace App\Controller;
use App\Entity\Discussion;
use App\Entity\User;

use App\Form\DiscussionType;
use App\Repository\DiscussionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChartController extends AbstractController
{
    #[Route('/chart', name: 'app_chart')]
    public function index(DiscussionRepository $discussionRepository,Request $request,PaginatorInterface $paginator): Response
    {
        $discussionsPerUser = $discussionRepository->getNumberOfDiscussionsPerUser();
        $totalDiscussions = $discussionRepository->getTotalDiscussions();
        $discussions = $discussionRepository->findAll();
        $discussions = $paginator->paginate(
            $discussions, /* query NOT result */
            $request->query->getInt('page', 1),
            4
        );
        return $this->render('chart/discussionCHart.html.twig', [
            'totalDiscussions' => $totalDiscussions,
            'discussions' => $discussions,
            'discussionsPerUser' => $discussionsPerUser,
        ]);
    }
    public function show(Request $request,DiscussionRepository $discussionRepository,PaginatorInterface $paginator):Response{
        $discussions = $discussionRepository->findAll();
        $discussions = $paginator->paginate(
            $discussions, /* query NOT result */
            $request->query->getInt('page', 1),
            4
        );
        return $this->render('discussion/listDiscussion.html.twig', [
            'discussions' => $discussions,
        ]);
        

    }
}
