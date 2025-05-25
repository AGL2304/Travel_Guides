<?php

namespace App\Controller;

use App\Entity\Visiteur;
use App\Form\VisiteurForm;
use App\Repository\VisiteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/visiteur')]
final class VisiteurController extends AbstractController
{
    #[Route(name: 'app_visiteur_index', methods: ['GET'])]
    public function index(VisiteurRepository $visiteurRepository): Response
    {
        return $this->render('visiteur/index.html.twig', [
            'visiteurs' => $visiteurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_visiteur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $visiteur = new Visiteur();
        $form = $this->createForm(VisiteurForm::class, $visiteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($visiteur);
            $entityManager->flush();

            return $this->redirectToRoute('app_visiteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visiteur/new.html.twig', [
            'visiteur' => $visiteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visiteur_show', methods: ['GET'])]
    public function show(Visiteur $visiteur): Response
    {
        return $this->render('visiteur/show.html.twig', [
            'visiteur' => $visiteur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_visiteur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Visiteur $visiteur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VisiteurForm::class, $visiteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_visiteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visiteur/edit.html.twig', [
            'visiteur' => $visiteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visiteur_delete', methods: ['POST'])]
    public function delete(Request $request, Visiteur $visiteur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$visiteur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($visiteur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_visiteur_index', [], Response::HTTP_SEE_OTHER);
    }
}
