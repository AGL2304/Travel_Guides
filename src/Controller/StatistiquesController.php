<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Guide;
use App\Entity\Visite;

final class StatistiquesController extends AbstractController
{
    #[Route('/statistiques', name: 'app_statistiques')]
    public function index(EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();

        // NOUVEAU : Définir la locale de la connexion AVANT d'exécuter les requêtes
        // Cela garantit que les noms des mois seront en français pour toutes les requêtes suivantes.
        try {
            $conn->executeStatement("SET lc_time_names = 'fr_FR'");
        } catch (\Exception $e) {
            // Optionnel : gérer l'erreur si la commande n'est pas supportée,
            // mais c'est généralement sans risque.
            // Par exemple, vous pouvez logger l'erreur.
        }

        // 1. Visites par mois (DATE_FORMAT avec 2 arguments)
        $sql1 = "
            SELECT 
                DATE_FORMAT(v.date, '%M %Y') AS mois,
                COUNT(*) AS total
            FROM visite v
            WHERE v.date IS NOT NULL
            GROUP BY mois, DATE_FORMAT(v.date, '%Y-%m') -- Ajouté pour un groupement robuste
            ORDER BY DATE_FORMAT(v.date, '%Y-%m')
        ";
        $visitesParMois = $conn->executeQuery($sql1)->fetchAllAssociative();

        // Calculer la valeur maximale pour le graphique à barres
        $maxVisitesMois = 0;
        foreach ($visitesParMois as $stat) {
            if ($stat['total'] > $maxVisitesMois) {
                $maxVisitesMois = $stat['total'];
            }
        }

        // 2. Visites par guide par mois (DATE_FORMAT avec 2 arguments)
        $sql2 = "
            SELECT 
                CONCAT(g.firstname, ' ', g.name) AS guide, -- Amélioration pour afficher le nom complet
                DATE_FORMAT(v.date, '%M %Y') AS mois,
                COUNT(*) AS total
            FROM visite v
            JOIN guide g ON v.guide_id = g.id
            WHERE v.date IS NOT NULL
            GROUP BY guide, mois, DATE_FORMAT(v.date, '%Y-%m')
            ORDER BY guide, DATE_FORMAT(v.date, '%Y-%m')
        ";
        $visitesParGuide = $conn->executeQuery($sql2)->fetchAllAssociative();

        // 3. Taux de présence des touristes (DATE_FORMAT avec 2 arguments)
        $sql3 = "
            SELECT 
                DATE_FORMAT(v.date, '%M %Y') AS mois,
                ROUND(SUM(CASE WHEN vi.present = 1 THEN 1 ELSE 0 END) / COUNT(*), 2) AS taux
            FROM visite v
            JOIN visiteur vi ON vi.visite_id = v.id
            WHERE v.date IS NOT NULL
            GROUP BY mois, DATE_FORMAT(v.date, '%Y-%m')
            ORDER BY DATE_FORMAT(v.date, '%Y-%m')
        ";
        $tauxPresence = $conn->executeQuery($sql3)->fetchAllAssociative();

        // Calcul des KPIs (Indicateurs Clés)
        $totalGuides = $em->getRepository(Guide::class)->count(['statut' => 'Actif']);
        $totalVisites = $em->getRepository(Visite::class)->count([]);

        return $this->render('statistiques/index.html.twig', [
            'visitesParMois' => $visitesParMois,
            'visitesParGuide' => $visitesParGuide,
            'tauxPresence' => $tauxPresence,
            'maxVisitesMois' => $maxVisitesMois,
            'totalGuides' => $totalGuides,
            'totalVisites' => $totalVisites,
        ]);
    }
}