<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;


final class StatistiquesController extends AbstractController
{
    #[Route('/statistiques', name: 'app_statistiques')]
    public function index(EntityManagerInterface $em): Response
    {
        // Nombre de visites par mois (groupé par mois/année)
        $visitesParMois = $em->createQuery(
            "SELECT 
            CONCAT(MONTH(v.date), '/', YEAR(v.date)) AS mois, 
            COUNT(v.id) AS total
         FROM App\Entity\Visite v
         GROUP BY mois
         ORDER BY YEAR(v.date), MONTH(v.date)"
        )->getResult();

        // Nombre de visites par guide et par mois
        $visitesParGuide = $em->createQuery(
            "SELECT 
            g.nom AS guide,
            CONCAT(MONTH(v.date), '/', YEAR(v.date)) AS mois,
            COUNT(v.id) AS total
         FROM App\Entity\Visite v
         JOIN v.guide g
         GROUP BY guide, mois
         ORDER BY guide, mois"
        )->getResult();

        // Taux de présence des touristes
        $tauxPresence = $em->createQuery(
            "SELECT 
            CONCAT(MONTH(v.date), '/', YEAR(v.date)) AS mois,
            SUM(CASE WHEN vi.present = true THEN 1 ELSE 0 END) * 1.0 / COUNT(vi.id) AS taux
         FROM App\Entity\Visite v
         JOIN v.visiteurs vi
         GROUP BY mois
         ORDER BY YEAR(v.date), MONTH(v.date)"
        )->getResult();

        return $this->render('statistiques/index.html.twig', [
            'visitesParMois' => $visitesParMois,
            'visitesParGuide' => $visitesParGuide,
            'tauxPresence' => $tauxPresence
        ]);
    }

}
