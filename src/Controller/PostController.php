<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Repository\PostRepository;
use App\Form\PostType;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class PostController extends AbstractController
{
    #[Route('/post/new', name: 'app_new_post', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentUser = $this->getUser();

            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setAuthor($currentUser);
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/load-more', methods: ['GET'], priority: 2)]
    public function loadMorePosts(Request $request, PostRepository $postRepository): JsonResponse
    {
        $offset = $request->query->getInt('offset', 0);
        $limit = 10;

        $posts = $postRepository->findPosts($offset, $limit);

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

    #[Route('/post/{id}', name: 'app_show_post', methods: ['GET', 'POST'], priority: 1, requirements: ['id' => '\d+'])]
    public function showPost(string $id, PostRepository $postRepository, Request $request, EntityManagerInterface $em): Response
    {
        if ($id) {
            $post = $postRepository->findPostWithLimitedComments($id, 10);

            if (!$post) {
                throw $this->createNotFoundException('Post not found');
            }

            $comment = new Comment();
            $form = $this->createForm(CommentType::class, $comment);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $comment->setPost($post);
                $comment->setAuthor($this->getUser());
                $comment->setCreatedAt(new \DateTimeImmutable());

                $em->persist($comment);
                $em->flush();

                return $this->redirectToRoute('app_show_post', ['id' => $id]);
            }

            return $this->render('post/show.html.twig', [
                'post' => $post,
                'form' => $form->createView(),
            ]);            
        }
    }

    #[Route('/post/{id}/like', name: 'app_post_like', methods: ['POST'])]
    public function likePost(string $id, EntityManagerInterface $em, Security $security, Request $request): Response
    {
        if ($id) {
            $post = $em->getRepository(Post::class)->findOneBy(['id' => $id]);
            $user = $this->getUser();

            if ($post) {
                $delay = 2;

                // Récup du temps du dernier like de l'utilisateur depuis la session
                $lastLikeTime = $request->getSession()->get('last_post_like_time_' . $id);

                // Si le délai n'est pas respecté
                if ($lastLikeTime && (time() - $lastLikeTime < $delay)) {
                    return new Response(null, Response::HTTP_TOO_MANY_REQUESTS); // Statut 429 pour "Trop de requêtes"
                }

                if (!$post->getLikedBy()->contains($user)) {
                    $post->addLikedBy($user);
                    $user->addLikedPost($post);

                    $em->persist($post);
                    $em->persist($user);
                    $em->flush();

                    // Mets à jour le temps du dernier like dans la session
                    $request->getSession()->set('last_post_like_time_' . $id, time());

                    return new Response(null, Response::HTTP_NO_CONTENT); // Statut 204 pour "liké avec succès"
                } 
                else {
                    // Si le post est déjà liké, renvoie un statut 409 (Conflict)
                    return new Response(null, Response::HTTP_CONFLICT); // Statut 409 pour indiquer un conflit
                }
            }
        }
    
        return new Response(null, Response::HTTP_NOT_FOUND); // Statut 404 si le post n'existe pas
    }

    #[Route('/post/{id}/unlike', name: 'app_post_unlike', methods: ['DELETE'])]
    public function unlikePost(string $id, EntityManagerInterface $em, Security $security, Request $request): Response
    {
        if ($id) {
            $post = $em->getRepository(Post::class)->findOneBy(['id' => $id]);
            $user = $this->getUser();

            if ($post) {
                $delay = 2;

                // Récup du temps du dernier like de l'utilisateur depuis la session
                $lastLikeTime = $request->getSession()->get('last_post_like_time_' . $id);

                // Si le délai n'est pas respecté
                if ($lastLikeTime && (time() - $lastLikeTime < $delay)) {
                    return new Response(null, Response::HTTP_TOO_MANY_REQUESTS); // Statut 429 pour "Trop de requêtes"
                }

                if ($post->getLikedBy()->contains($user)) {
                    $post->removeLikedBy($user);
                    $user->removeLikedPost($post);

                    $em->persist($post);
                    $em->persist($user);
                    $em->flush();

                    // Mets à jour le temps du dernier like dans la session
                    $request->getSession()->set('last_post_like_time_' . $id, time());

                    return new Response(null, Response::HTTP_NO_CONTENT); // Statut 204 pour "unliké avec succès"
                } 
                else {
                    // Si le post est déjà unliké, renvoie un statut 409 (Conflict)
                    return new Response(null, Response::HTTP_CONFLICT); // Statut 409 pour indiquer un conflit
                }
            }
        }
    
        return new Response(null, Response::HTTP_NOT_FOUND); // Statut 404 si le post n'existe pas
    }
}
