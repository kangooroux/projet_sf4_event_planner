<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EventFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function loadData()
    {
        $this->createMany(50, 'event', function (){
            $event = new Event;
            $event
                ->setName($this->faker->catchPhrase)
                ->setDescription($this->faker->realText(200))
                ->setDate($this->faker->dateTimeBetween('-13 months', '13 months'))
                ->setLocation($this->faker->address)
                ->setAuthor($this->getRandomReference('user_user'))
            ;
            $randomValue = rand(0,10);
            for ($i = 0; $i < $randomValue; $i++) {
                $event->addAttend($this->getUniqueRandomReference('user_user'));
            }

            return $event;
        });
    }


    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }
}
