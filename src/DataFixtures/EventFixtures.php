<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EventFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function loadData()
    {
        $this->createMany(50, 'event', function (){
            return (new Event())
                ->setName($this->faker->catchPhrase)
                ->setDescription($this->faker->realText(200))
                ->setDate($this->faker->dateTimeBetween('-13 months', '13 months'))
                ->setLocation($this->faker->address)
                ->setAuthor($this->getRandomReference('user_user'))
            ;
        });
    }


    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }
}
