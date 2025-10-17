<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/post/{id}/load-more-comments', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function loadMoreComments(string $id, CommentRepository $commentRepository, Request $request): JsonResponse
    {
        if ($id) {
            $offset = $request->query->getInt('offset', 0);
            $limit = 10;

            $comments = $commentRepository->findMoreCommentsByPost($id, $offset, $limit);

            // Vérifie s'il n'y a plus de commentaires à charger
            $totalComments = $commentRepository->getCommentsCountByPost($id);
            $hasMore = ($offset + $limit) < $totalComments;
    
            // Rendu des posts avec Twig
            $html = $this->renderView('post/_comments.html.twig', [
                'comments' => $comments,
            ]);
    
            return new JsonResponse([
                'html' => $html,
                'hasMore' => $hasMore,
            ]);        
        }
    }

    #[Route('/comment/{id}/like', name: 'app_comment_like', methods: ['POST'])]
    public function likeComment(string $id, EntityManagerInterface $em, Request $request): Response
    {
        if ($id) {
            $comment = $em->getRepository(Comment::class)->findOneBy(['id' => $id]);
            $user = $this->getUser();

            if ($comment) {
                $delay = 2;

                // Récup du temps du dernier like de l'utilisateur depuis la session
                $lastLikeTime = $request->getSession()->get('last_comment_like_time_' . $id);

                // Si le délai n'est pas respecté
                if ($lastLikeTime && (time() - $lastLikeTime < $delay)) {
                    return new Response(null, Response::HTTP_TOO_MANY_REQUESTS); // Statut 429 pour "Trop de requêtes"
                }

                if (!$comment->getLikedBy()->contains($user)) {
                    $comment->addLikedBy($user);
                    $user->addLikedComment($comment);

                    $em->persist($comment);
                    $em->persist($user);
                    $em->flush();

                    // Mets à jour le temps du dernier like dans la session
                    $request->getSession()->set('last_comment_like_time_' . $id, time());

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

    #[Route('/comment/{id}/unlike', name: 'app_comment_unlike', methods: ['DELETE'])]
    public function unlikeComment(string $id, EntityManagerInterface $em, Request $request): Response
    {
        if ($id) {
            $comment = $em->getRepository(Comment::class)->findOneBy(['id' => $id]);
            $user = $this->getUser();

            if ($comment) {
                $delay = 2;

                // Récup du temps du dernier like de l'utilisateur depuis la session
                $lastLikeTime = $request->getSession()->get('last_comment_like_time_' . $id);

                // Si le délai n'est pas respecté
                if ($lastLikeTime && (time() - $lastLikeTime < $delay)) {
                    return new Response(null, Response::HTTP_TOO_MANY_REQUESTS); // Statut 429 pour "Trop de requêtes"
                }

                if ($comment->getLikedBy()->contains($user)) {
                    $comment->removeLikedBy($user);
                    $user->removeLikedComment($comment);

                    $em->persist($comment);
                    $em->persist($user);
                    $em->flush();

                    // Mets à jour le temps du dernier like dans la session
                    $request->getSession()->set('last_comment_like_time_' . $id, time());

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
