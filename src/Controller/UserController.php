<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;

use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\PostRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class UserController extends AbstractController
{

    #[Route('/profile/settings', name: 'app_profile_settings', methods: ['GET', 'POST'], priority: 2)]
    public function settings(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $currentUser = $this->getUser();

        $form = $this->createForm(UserType::class, $currentUser);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {            
            if ($form->isValid()) {
                $entityManager->flush();
                $currentUser->setUpdatedAt(new \DateTimeImmutable());
                
                return $this->redirectToRoute('app_profile_show');
            }
        }

        $userProfileData = [
            'isAdmin' => in_array("ROLE_ADMIN", $currentUser->getRoles()),
            'username' => $currentUser->getUsername(),
            'description' => $currentUser->getDescription(),
            'profileBackgroundLink' => $currentUser->getProfileBackgroundLink(),
            'profilePictureLink' => $currentUser->getProfilePictureLink(),
            'profileBorderColor' => $currentUser->getProfileBorderColor(),
        ];

        return $this->render('user/settings.html.twig', [
            'form' => $form->createView(),
            'user' => $userProfileData,
        ]);
    }

    #[Route('/profile/{username}', name: 'app_profile_show', methods: ['GET'], priority: 1)]
    public function profile(EntityManagerInterface $entityManager, PostRepository $postRepository, string $username = null): Response
    {
        // Si utilisateur fourni dans la route
        if ($username) {
            $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            
            if (!$user) {
                throw $this->createNotFoundException('The user does not exist');
            }

            $userProfileData = [
                'username' => $user->getUsername(),
                'description' => $user->getDescription(),
                'profilePictureLink' => $user->getProfilePictureLink(),
                'profileBackgroundLink' => $user->getProfileBackgroundLink(),
                'profileBorderColor' => $user->getProfileBorderColor(),
                'posts' => $postRepository->findFirstTenPostsByUser($user),
                'differentUser' => $user->getUsername() != $this->getUser()->getUsername()
            ];

            return $this->render('user/profile.html.twig', [
                'userData' => $userProfileData
            ]);            
        }
        else {
            $currentUser = $this->getUser();
    
            // Redirige vers le profil de l'utilisateur actuel
            return $this->redirectToRoute('app_profile_show', ['username' => $currentUser->getUsername()]);
        }
    }

    #[Route('/profile/{username}/load-more-posts', methods: ['GET'])]
    public function loadMoreUserPosts(Request $request, PostRepository $postRepository, UserRepository $userRepository, string $username = null): JsonResponse
    {
        if ($username) {
            $offset = $request->query->getInt('offset', 0);
            $limit = 10;

            $user = $userRepository->findOneBy(['username' => $username]);
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], 404);
            }

            // Récupère les posts de cet utilisateur
            $posts = $postRepository->findPostsByUser($user, $offset, $limit);

            // Vérifie s'il n'y a plus de posts à charger
            $totalPosts = $postRepository->count([]);
            $hasMore = ($offset + $limit) < $totalPosts;

            // Rendu des posts avec Twig
            $html = $this->renderView('post/_posts.html.twig', [
                'posts' => $posts,
            ]);

            return new JsonResponse([
                'html' => $html,
                'hasMore' => $hasMore,
            ]);            
        }
    }
}
