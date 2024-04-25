<?php

namespace App\Controller;

use App\Repository\NoteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(NoteRepository $noteRepository): Response
    {
        return $this->render('index/index.html.twig', [
            'notes' => $noteRepository->findAll(),
        ]);
    }
}
