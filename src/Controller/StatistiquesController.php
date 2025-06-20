<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Guide;
use App\Entity\Visite;
use App\Entity\Visiteur; // Ajout requis pour le type hinting du repository
use App\Repository\VisiteurRepository; // Ajout requis pour l'injection

final class StatistiquesController extends AbstractController
{
    #[Route('/statistiques', name: 'app_statistiques')]
    public function index(EntityManagerInterface $em, VisiteurRepository $visiteurRepository): Response // Ajout de VisiteurRepository
    {
        $conn = $em->getConnection();

        // Définir la locale de la connexion pour avoir les mois en français
        try {
            $conn->executeStatement("SET lc_time_names = 'fr_FR'");
        } catch (\Exception $e) {
            // Optionnel : gérer l'erreur si la commande n'est pas supportée.
        }

        // 1. Visites par mois
        $sql1 = "
            SELECT 
                DATE_FORMAT(v.date, '%M %Y') AS mois,
                COUNT(*) AS total
            FROM visite v
            WHERE v.date IS NOT NULL
            GROUP BY mois, DATE_FORMAT(v.date, '%Y-%m')
            ORDER BY DATE_FORMAT(v.date, '%Y-%m')
        ";
        $visitesParMois = $conn->executeQuery($sql1)->fetchAllAssociative();

        // Calculer la valeur maximale pour le graphique à barres
        $maxVisitesMois = 0;
        if (!empty($visitesParMois)) {
            $maxVisitesMois = max(array_column($visitesParMois, 'total'));
        }

        // 2. Visites par guide par mois
        $sql2 = "
            SELECT 
                g.id AS guide_id,
                CONCAT(g.firstname, ' ', g.name) AS guide,
                DATE_FORMAT(v.date, '%M %Y') AS mois,
                COUNT(*) AS total
            FROM visite v
            JOIN guide g ON v.guide_id = g.id
            WHERE v.date IS NOT NULL
            GROUP BY g.id, guide, mois, DATE_FORMAT(v.date, '%Y-%m')
            ORDER BY guide, DATE_FORMAT(v.date, '%Y-%m')

        ";
        $visitesParGuide = $conn->executeQuery($sql2)->fetchAllAssociative();

        // 3. Taux de présence des touristes par mois
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

        // --- SECTION DES INDICATEURS CLÉS (KPIs) ---
        
        // Indicateurs existants
        $totalGuides = $em->getRepository(Guide::class)->count(['statut' => 'Actif']);
        $totalVisites = $em->getRepository(Visite::class)->count([]);

        // NOUVEAU : Calcul du taux de présence global
        $totalPresents = $visiteurRepository->count(['present' => true]);
        $totalVisiteurs = $visiteurRepository->count([]);
        $globalPresenceRate = ($totalVisiteurs > 0) ? ($totalPresents / $totalVisiteurs) : 0;
        
        // NOUVEAU : Calcul des visites pour le mois en cours
        $sqlVisitesMois = "
            SELECT COUNT(*) 
            FROM visite 
            WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())
        ";
        // fetchOne() récupère la première colonne de la première ligne
        $visitesMoisActuel = $conn->executeQuery($sqlVisitesMois)->fetchOne();

        // --- FIN DE LA SECTION KPIs ---

        return $this->render('statistiques/index.html.twig', [
            'visitesParMois' => $visitesParMois,
            'visitesParGuide' => $visitesParGuide,
            'tauxPresence' => $tauxPresence,
            'maxVisitesMois' => $maxVisitesMois,
            'totalGuides' => $totalGuides,
            'totalVisites' => $totalVisites,
            
            // Les nouvelles variables requises par le template
            'globalPresenceRate' => $globalPresenceRate,
            'visitesMoisActuel' => $visitesMoisActuel,
        ]);
    }
}