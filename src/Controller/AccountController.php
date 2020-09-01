<?php

namespace App\Controller;

use App\Form\UpdatePasswordFormType;
use App\Form\UpdateProfileTypeFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/account", name="account_")
 * @isGranted("ROLE_USER")
 */
class AccountController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("", name="page")
     */
    public function accountPage(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();

        $updateProfileForm = $this->createForm(UpdateProfileTypeFormType::class, $user);
        $updateProfileForm->handleRequest($request);

        $updatePasswordForm = $this->createForm(UpdatePasswordFormType::class, $user);
        $updatePasswordForm->handleRequest($request);

        if ($updateProfileForm->isSubmitted() && $updateProfileForm->isValid()) {

            $newPseudo = $updateProfileForm->get('pseudo')->getData();
            $newEmail = $updateProfileForm->get('email')->getData();

            $user
                ->setPseudo($newPseudo)
                ->setEmail($newEmail)
            ;

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre profil a été modifié avec succès');
            return $this->redirectToRoute('account_page');

        } elseif ($updatePasswordForm->isSubmitted() && $updatePasswordForm->isValid()) {
            $newPassword = $updatePasswordForm->get('newPassword')->getData();

            $user->setPassword($passwordEncoder->encodePassword($user, $newPassword));

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès');
            return $this->redirectToRoute('account_page');
        }

        return $this->render('account/account.html.twig', [
            'updatePasswordForm' => $updatePasswordForm->createView(),
            'updateProfileForm' => $updateProfileForm->createView()
        ]);
    }
}
