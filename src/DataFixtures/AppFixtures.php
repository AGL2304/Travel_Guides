<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Guide;
use App\Entity\Visite;
use App\Entity\Visiteur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Utilisation du locale français

        // --- USERS ---
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setName($faker->lastName());
            $user->setFirstname($faker->firstName()); // Ajout du firstname manquant
            $user->setEmail($faker->unique()->safeEmail());

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);

            $roles = $faker->boolean(20) ? ['ROLE_ADMIN'] : ['ROLE_USER'];
            $user->setRoles($roles);

            $manager->persist($user);
        }

        // --- GUIDES ---
        $guides = [];
        $pays = ['France', 'Espagne', 'Italie', 'Allemagne', 'Royaume-Uni', 'Portugal', 'Grèce', 'Suisse'];
        
        for ($i = 0; $i < 8; $i++) {
            $guide = new Guide();
            $guide->setName($faker->lastName());
            $guide->setFirstname($faker->firstName());
            $guide->setStatut($faker->randomElement(['actif', 'inactif', 'en congé']));
            $guide->setPays($faker->randomElement($pays));
            $guide->setPhoto($faker->imageUrl(200, 200, 'people'));
            $guide->setOneToMany($faker->sentence()); // Champ OneToMany avec majuscule

            $manager->persist($guide);
            $guides[] = $guide;
        }

        // --- VISITES ---
        $visites = [];
        $lieux = [
            'Paris' => 'France',
            'Madrid' => 'Espagne', 
            'Rome' => 'Italie',
            'Berlin' => 'Allemagne',
            'Londres' => 'Royaume-Uni',
            'Lisbonne' => 'Portugal',
            'Athènes' => 'Grèce',
            'Zurich' => 'Suisse'
        ];

        for ($i = 0; $i < 25; $i++) {
            $visite = new Visite();
            $visite->setPhoto($faker->imageUrl(640, 480, 'travel'));
            
            // Sélection cohérente lieu/pays
            $lieu = $faker->randomElement(array_keys($lieux));
            $visite->setLieu($lieu);
            $visite->setPays($lieux[$lieu]);
            
            // Date dans les 6 prochains mois
            $date = $faker->dateTimeBetween('now', '+6 months');
            $visite->setDate($date);

            // Heure de début (format TIME pour MySQL)
            $heureDebut = $faker->time('H:i:s');
            $visite->setHeureDebut(\DateTime::createFromFormat('H:i:s', $heureDebut));

            // Durée en heures (1 à 6 heures)
            $duree = $faker->numberBetween(1, 6);
            $visite->setDuree($duree);

            // Calcul de l'heure de fin
            $heureDebutObj = \DateTime::createFromFormat('H:i:s', $heureDebut);
            $heureFin = (clone $heureDebutObj)->modify("+$duree hours");
            $visite->setHeureFin($heureFin);

            $visite->setCommentaireGeneral($faker->optional(0.7)->paragraph());
            
            // Sélection d'un guide du même pays si possible
            $guidesDisponibles = array_filter($guides, function($guide) use ($visite) {
                return $guide->getPays() === $visite->getPays() && $guide->getStatut() === 'actif';
            });
            
            if (empty($guidesDisponibles)) {
                $guidesDisponibles = array_filter($guides, function($guide) {
                    return $guide->getStatut() === 'actif';
                });
            }
            
            if (!empty($guidesDisponibles)) {
                $visite->setGuide($faker->randomElement($guidesDisponibles));
            } else {
                $visite->setGuide($faker->randomElement($guides));
            }

            $visite->setVisite($faker->sentence()); // Champ visite - nom de la visite/activité

            $manager->persist($visite);
            $visites[] = $visite;
        }

        // --- VISITEURS ---
        for ($i = 0; $i < 60; $i++) {
            $visiteur = new Visiteur();
            $visiteur->setName($faker->lastName());
            $visiteur->setFirstname($faker->firstName());
            $visiteur->setPresent($faker->boolean(85)); // 85% de chance d'être présent
            $visiteur->setCommentaire($faker->optional(0.3)->sentence());

            // Relier à une visite existante aléatoire
            $visiteur->setVisite($faker->randomElement($visites));

            $manager->persist($visiteur);
        }

        $manager->flush();
    }
}