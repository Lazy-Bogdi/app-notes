<?php

namespace App\Controller;

use DateTime;
use App\Entity\Note;
use App\Form\NoteType;
use DateTimeImmutable;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/note')]
class NoteController extends AbstractController
{
    #[Route('/', name: 'app_note_index', methods: ['GET'])]
    public function index(NoteRepository $noteRepository, PaginatorInterface $paginator, Request $request): Response
    {
    
        $queryBuilder = $noteRepository->createQueryBuilder('n');

        $pagination = $paginator->paginate(
            $queryBuilder, 
            $request->query->getInt('page', 1), 
            9
        );

        return $this->render('note/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/my-notes', name: 'app_my_notes', methods: ['GET'])]
    public function userNotes(NoteRepository $noteRepository, PaginatorInterface $paginator, Request $request): Response
    {
        
        $currentUser = $this->getUser();
        if ($currentUser === null) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        $queryBuilder = $noteRepository->createQueryBuilder('n')
            ->where('n.owner = :owner')
            ->setParameter('owner', $currentUser)
            ->orderBy('n.createdAt', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder, 
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('note/index-2.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/new', name: 'app_note_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (empty($this->getUser())) {
            $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        $note = new Note();
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $note->setOwner($this->getUser());
            $note->setCreatedAt(new DateTimeImmutable());
            $note->setUpdatedAt($note->getCreatedAt());
            $entityManager->persist($note);
            $entityManager->flush();

            return $this->redirectToRoute('app_my_notes', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/new.html.twig', [
            'note' => $note,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_note_show', methods: ['GET'])]
    public function show(Note $note): Response
    {
        return $this->render('note/show.html.twig', [
            'note' => $note,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_note_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Note $note, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($note->getCreatedAt() == null) {
                $note->setCreatedAt(new DateTimeImmutable());
            }
            $note->setUpdatedAt(new DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_my_notes', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/edit.html.twig', [
            'note' => $note,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_note_delete', methods: ['POST'])]
    public function delete(Request $request, Note $note, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $note->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($note);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_my_notes', [], Response::HTTP_SEE_OTHER);
    }
}
