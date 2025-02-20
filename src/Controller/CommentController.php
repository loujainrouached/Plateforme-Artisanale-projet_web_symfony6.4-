<?php

namespace App\Controller;
use App\Entity\Comment;
use App\Entity\Article;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;


final class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findAll();
        return $this->render('comment/index.html.twig', [
            'comments' => $comments,
        ]);
    }
    
  /*  #[Route('/article/{id}', name: 'article_details')]

public function show(ArticleRepository $articleRepository, int $id): Response
{
    $article = $articleRepository->find($id);

    if (!$article) {
        throw $this->createNotFoundException("L'article avec l'ID $id n'existe pas.");
    }

    // Récupérer les articles similaires (même catégorie, exclure l'article en cours)
    $recentarticles = $articleRepository->findBy(
        ['categorie' => $article->getCategorie()],
        ['datepub' => 'DESC'],
        3 // Nombre d'articles similaires
    );

    return $this->render('comment/details.html.twig', [
        'article' => $article,
        'recent_articles' => $recentarticles,
    

    ]);
}
    #[Route('/comment/add/{id}', name: 'add_comment', methods: ['POST'])]
    public function addComment(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        // 🔹 On récupère l'utilisateur ayant l'ID 1 (ajouté manuellement en base)
        $user = $entityManager->getRepository(User::class)->find(1);
    
        if (!$user) {
            throw $this->createNotFoundException("Utilisateur avec l'ID 1 introuvable.");
        }
    
        // 🔹 Récupération du contenu du commentaire et du rating
        $contenu = $request->request->get('contenuComment');
        $rating = $request->request->get('rating'); // Récupération de la note (1 à 5 étoiles)
    
        // 🔹 Vérification du contenu
        if (empty($contenu)) {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
            return $this->redirectToRoute('article_details', ['id' => $article->getId()]);
        }
    
        // 🔹 Création du commentaire
        $comment = new Comment();
        $comment->setContenuComment($contenu);
        $comment->setDatecom(new \DateTime());
        $comment->setUser($user); // Associe l'utilisateur ayant ID=1
        $comment->setArticle($article);
    
        // 🔹 Stockage de la note (si fournie)
        if (!empty($rating) && is_numeric($rating) && $rating >= 1 && $rating <= 5) {
            $comment->setRating((int)$rating);
        }
    
        // 🔹 Sauvegarde en BDD
        $entityManager->persist($comment);
        $entityManager->flush();
    
        $this->addFlash('success', 'Commentaire ajouté avec succès.');
    
        return $this->redirectToRoute('article_details', ['id' => $article->getId()]);
    }*/
    #[Route('/article/{id}', name: 'article_details')]
public function show(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager, int $id): Response
{
    $article = $articleRepository->find($id);

    if (!$article) {
        throw $this->createNotFoundException("L'article avec l'ID $id n'existe pas.");
    }
    $user =$this->getUser();
    // ✅ Créer un nouveau commentaire et son formulaire
    $comment = new Comment();
    $form = $this->createForm(CommentType::class, $comment);
    $form->handleRequest($request);

    // ✅ Vérifier si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        $user = $entityManager->getRepository(User::class)->find($user->getId()); // Simule un utilisateur ID=1
        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        // Associer le commentaire à l'utilisateur et l'article
        $comment->setUser($user);
        $comment->setArticle($article);
        $comment->setDateCom(new \DateTime());

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire ajouté avec succès.');

        return $this->redirectToRoute('article_details', ['id' => $article->getId()]);
    }

    // ✅ Récupérer les articles similaires
    $recentArticles = $articleRepository->findBy(
        ['categorie' => $article->getCategorie()],
        ['datepub' => 'DESC'],
        3 // Nombre d'articles similaires
    );

    return $this->render('comment/details.html.twig', [
        'article' => $article,
        'recent_articles' => $recentArticles,
        'form' => $form->createView(), // ✅ Ajout du formulaire

    ]);
}

    
    #[Route('/comment/edit/{id}', name: 'edit_comment_page', methods: ['POST'])]
public function editComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
{
    // Simule un utilisateur connecté avec ID = 1 (À remplacer par l'authentification)
    $user =$this->getUser();
    $user = $entityManager->getRepository(User::class)->find($user->getId());
    

    // Vérifie que l'utilisateur a le droit de modifier le commentaire
    if (!$user || $comment->getUser()->getId() !== $user->getId()) {
        return new JsonResponse(['status' => 'error', 'message' => 'Non autorisé'], 403);
    }

    // Récupère le nouveau contenu
    $newContent = $request->request->get('contenuComment');

    if (!empty($newContent)) {
        $comment->setContenuComment($newContent);
        $comment->setDateCom(new \DateTime()); // Mise à jour de la date
        $entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'newContent' => $newContent
        ]);
    }

    return new JsonResponse(['status' => 'error', 'message' => 'Le commentaire ne peut pas être vide'], 400);
}

#[Route('/comment/delete/{id}', name: 'delete_comment', methods: ['POST'])]
public function deleteComment(Comment $comment, EntityManagerInterface $entityManager, Request $request): JsonResponse
{
    // Vérifier le token CSRF pour éviter les attaques CSRF
    if (!$this->isCsrfTokenValid('delete_comment_' . $comment->getId(), $request->request->get('_token'))) {
        return new JsonResponse(['status' => 'error', 'message' => 'Token CSRF invalide'], 400);
    }
    

    // Vérifier l'utilisateur (à remplacer par l'authentification réelle)
    $user =$this->getUser();
    $user = $entityManager->getRepository(User::class)->find($user->getId());
    if (!$user || $comment->getUser()->getId() !== $user->getId()) {
        return new JsonResponse(['status' => 'error', 'message' => 'Non autorisé'], 403);
    }

    $entityManager->remove($comment);
    $entityManager->flush();

    return new JsonResponse(['status' => 'success']);
}


    #[Route('/deleteComment/{id}', name: 'delete_comment')]
    public function deleteCommentback(Comment $comment, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
    
        if (!$comment) {
            throw $this->createNotFoundException('Commentaire non trouvé.');
        }
    
        // Supprimer le commentaire
        $em->remove($comment);
        $em->flush();
    
        // Rediriger vers la liste des commentaires après la suppression
        return $this->redirectToRoute('app_comment');
    }
    
}