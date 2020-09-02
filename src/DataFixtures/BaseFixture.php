<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

/**
 * Class BaseFixture
 * Modèle pour les fixtures, on ne peut pas instancier une abstraction
 * @package App\DataFixtures
 */
abstract class BaseFixture extends Fixture {

    /**
     * @var ObjectManager
     */
    private $manager;
    /**
     * @var Generator
     */
    protected $faker;
    /**
     * @var array liste des références connues, mémoïsation
     */
    private $references = [];

    /**
     * Méthode à implémenter pour les classes qui héritent de celle-ci et générera les données
     */
    abstract protected function loadData();

    /**
     * Méthode appelée par le système de fixtures
     */
    public function load(ObjectManager $manager)
    {
        //On va enregistrer le ObjectManager
        $this->manager = $manager;
        //On instancie Faker
        $this->faker = Factory::create('fr_FR');

        //on appelle loadData() pour générer les fausses données
        $this->loadData();
        //on éxecute l'enregistrement en base
        $this->manager->flush();
    }

    /**
     * Enregistre plusieurs entités
     *
     * Nombre d'entités à générer
     * @param int $count
     *
     * Nom du groupe de référence
     * @param string $groupName
     *
     * Fonction qui génère une entité
     * @param callable $factory
     */
    protected function createMany(int $count, string $groupName, callable $factory)
    {
        for ($i = 0;$i < $count;$i++) {
            //on éxecute $factoryqui doit retourner l'entité générée
            $entity = $factory($i);

            //vérifier que l'entité ait bien été retournée
            if ($entity === null) {
                throw new \LogicException('L\'entité doit être retourné. Auriez-vous oublié un "return" ?');
            }

            //on prépare à l'enregistrement de l'entité
            $this->manager->persist($entity);

            //Enregistre une référence à l'entité
            $reference = sprintf('%s_%d', $groupName, $i);
            $this->addReference($reference, $entity);
        }
    }

    /**
     * méthode pour récupérer une entité par son groupe de références
     * @param string $groupName nom de groupe de référence
     */
    protected function getRandomReference(string $groupName)
    {
        // Vérifier si on a dèjà enregistrer les références du groupe demandé
        if (!isset($this->references[$groupName])) {
            // Si non on va rechercher les références
            $this->references[$groupName] = [];

            // On parcourt la liste de toutes les références (toutes classes confondues)
            foreach ($this->referenceRepository->getReferences() as $key => $ref) {
                // $key correspond à nos références
                if (strpos($key, $groupName) === 0) {
                    $this->references[$groupName][] = $key;
                }
            }
        }

        //Vérifier que l'on a récupéré des références
        if ($this->references[$groupName] === []) {
            throw new \Exception(sprintf('Aucune références trouvé pour le groupe "%s"', $groupName));
        }

        //retourner une entité correspondant à une référence aléatoire
        $randomReference = $this->faker->randomElement($this->references[$groupName]);
        return $this->getReference($randomReference);
    }

    /**
     * méthode pour récupérer une entité unique par son groupe de références
     * @param string $groupName nom de groupe de référence
     */
    protected function getUniqueRandomReference(string $groupName)
    {
        // Vérifier si on a dèjà enregistrer les références du groupe demandé
        if (!isset($this->references[$groupName])) {
            // Si non on va rechercher les références
            $this->references[$groupName] = [];

            // On parcourt la liste de toutes les références (toutes classes confondues)
            foreach ($this->referenceRepository->getReferences() as $key => $ref) {
                // $key correspond à nos références
                if (strpos($key, $groupName) === 0) {
                    $this->references[$groupName][] = $key;
                }
            }
        }

        //Vérifier que l'on a récupéré des références
        if ($this->references[$groupName] === []) {
            throw new \Exception(sprintf('Aucune références trouvé pour le groupe "%s"', $groupName));
        }

        //retourner une entité correspondant à une référence aléatoire unique
        $randomReference = $this->faker->unique()->randomElement($this->references[$groupName]);
        return $this->getReference($randomReference);
    }
}