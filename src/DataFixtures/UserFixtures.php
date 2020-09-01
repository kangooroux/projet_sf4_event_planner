<?php


namespace App\DataFixtures;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixture
{
    private $encoder;

    /**
     * UserFixtures constructor.
     * Dans une classe (autre qu'un controlleur), on peut récupérer des services par autowiring uniquement dans le constructeur
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function loadData()
    {
        // adiministrateur
        $this->createMany(5, 'user_admin', function (int $i) {
            $admin = new User();
            $password = $this->encoder->encodePassword($admin, 'admin' . $i);

            return $admin
                ->setEmail('admin' . $i . '@test.com')
                ->setRoles(['ROLE_ADMIN'])
                ->setPassword($password)
                ->setPseudo($this->faker->unique()->userName)
                ;
        } );

        // utilisateurs
        $this->createMany(20, 'user_user', function (int $i) {
            $user = new User();
            $password = $this->encoder->encodePassword($user, 'user' . $i);

            return $user
                ->setEmail('user' . $i . '@test.com')
                ->setPassword($password)
                ->setPseudo($this->faker->unique()->userName)
                ;
        } );


    }
}