<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/staff')]
#[IsGranted('ROLE_ADMIN')]
class StaffController extends AbstractController
{
    #[Route('', name: 'app_staff_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $staff = $userRepository->findBy(['role' => 'staff'], ['id' => 'DESC']);
        return $this->render('staff/index.html.twig', [
            'staff' => $staff,
        ]);
    }

    #[Route('/new', name: 'app_staff_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher
    ): Response {
        if ($request->isMethod('POST')) {
            $username = strtolower(trim($request->request->get('username', '')));
            $fullName = trim($request->request->get('full_name', ''));
            $email = strtolower(trim($request->request->get('email', '')));
            $password = $request->request->get('password', '');

            if (!$username || !$email || !$password) {
                $this->addFlash('error', 'Username, email, and password are required.');
            } elseif ($username === 'admin' || $email === 'admin@baistock.com') {
                $this->addFlash('error', 'Cannot use reserved administrator username or email.');
            } elseif ($userRepository->findOneBy(['username' => $username])) {
                $this->addFlash('error', 'Username is already in use.');
            } elseif ($userRepository->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Email is already registered.');
            } else {
                $user = new User();
                $user->setUsername($username)
                    ->setFullName($fullName ?: $username)
                    ->setEmail($email)
                    ->setRole('staff')
                    ->setStatus('active')
                    ->setPassword($hasher->hashPassword($user, $password));

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', "Staff member '{$user->getFullName()}' created successfully.");
                return $this->redirectToRoute('app_staff_index');
            }
        }

        return $this->render('staff/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'app_staff_edit', methods: ['GET', 'POST'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher
    ): Response {
        // Ensure admin doesn't demote or edit fixed admin here
        if ($user->getUsername() === 'admin') {
            $this->addFlash('error', 'The primary administrator account cannot be edited from staff management.');
            return $this->redirectToRoute('app_staff_index');
        }

        if ($request->isMethod('POST')) {
            $fullName = trim($request->request->get('full_name', ''));
            $email = strtolower(trim($request->request->get('email', '')));
            $status = $request->request->get('status', 'active');
            $newPassword = $request->request->get('password', '');

            if (!$email) {
                $this->addFlash('error', 'Email is required.');
            } else {
                $existingWithEmail = $userRepository->findOneBy(['email' => $email]);
                if ($existingWithEmail && $existingWithEmail->getId() !== $user->getId()) {
                    $this->addFlash('error', 'Email is already used by another account.');
                } else {
                    $user->setFullName($fullName ?: $user->getUsername())
                        ->setEmail($email)
                        ->setStatus($status === 'inactive' ? 'inactive' : 'active');

                    // If admin provided a new password, change it
                    if (!empty($newPassword)) {
                        $user->setPassword($hasher->hashPassword($user, $newPassword));
                    }

                    $em->flush();
                    $this->addFlash('success', "Staff account '{$user->getUsername()}' updated successfully.");
                    return $this->redirectToRoute('app_staff_index');
                }
            }
        }

        return $this->render('staff/edit.html.twig', [
            'staff' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_staff_delete', methods: ['POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($user->getUsername() === 'admin') {
            $this->addFlash('error', 'The primary administrator cannot be deleted.');
            return $this->redirectToRoute('app_staff_index');
        }

        if ($this->isCsrfTokenValid('delete_staff_' . $user->getId(), $request->request->get('_token'))) {
            $username = $user->getUsername();
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', "Staff account '$username' deleted.");
        }

        return $this->redirectToRoute('app_staff_index');
    }
}
