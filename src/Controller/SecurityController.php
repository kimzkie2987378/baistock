<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/demo-login/{role}', name: 'app_demo_login')]
    public function demoLogin(
        string $role,
        UserRepository $userRepository,
        Security $security
    ): Response {
        $targetRole = strtolower($role);
        $username = match ($targetRole) {
            'owner' => 'owner',
            'staff' => 'staff',
            default => 'admin',
        };

        $user = $userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            $this->addFlash('error', "Demo user '$username' not found. Please seed data.");
            return $this->redirectToRoute('app_login');
        }

        $security->login($user);
        $this->addFlash('success', "Logged in successfully as {$user->getFullName()} ({$user->getRoleLabel()})");

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->redirectToRoute('app_login');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepted by the firewall.');
    }
}
