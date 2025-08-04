<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class PrivateController extends AbstractController
{
    
    #[Route('/private/article/', name: 'app_private')]
    public function index(): Response
    {
        // On récupére l'utilisateur connecté
        $user = $this->getUser();

        // On vérifie que l'utilisateur est connecté
        if (!$user instanceof \App\Entity\User) {
            throw $this->redirectToRoute('app_login');
        }

        // On récupére la liste de tous les articles écrit par l'utilisateur connecté
        $articles = $user->getArticles();

        // On récupére la liste de tous les articles publiés
        $published_articles = []; // list des articles publiés
        $unpublished_articles = []; // list des articles non-publiés

        foreach($articles as $article){
            if($article->getUser() === $user){
                if($article->isPublication()){
                    $published_articles[] = $article;
                } else {
                    $unpublished_articles[] = $article;
                }
            }

        }

        return $this->render('private/user_homepage.html.twig', [
            'controller_name' => 'ProduitController',
            'articles' => $articles,
            'published_articles' => $published_articles,
            'unpublished_articles' => $unpublished_articles,
            'user' => $user,
        ]);
    }

    #[Route('/private/user_edit', name: 'user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {

        // On récupére l'utilisateur connecté
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->redirectToRoute('app_login');;
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $form->get('password')->getData();

            if ($plainPassword) {
                // Ici, on hash le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // On récupére l'image issu du formulaire
            $imageFile = $form->get('image')->getData();

            // Si une image a bien été envoyé
            if($imageFile){

                // Suppression de l’ancienne image si existante
                if ($user->getImage() && file_exists($this->getParameter('images_directory').'/'.$user->getImage())) {
                    unlink($this->getParameter('images_directory').'/'.$user->getImage());
                }

                // On donne un nom de fichier à l'image avec uniqid pour avoir un nom unique
                //      et on ajoute l'extension devinée avec la function guessExtension()
                $FileName = uniqid().'.'.$imageFile->guessExtension();
                
                // On envoi le fichier dans le dossier prévu pour les images
                $imageFile->move(
                    $this->getParameter('images_directory'),$FileName
                );

                // On enregistre le nom du fichier dans l'entité Produit
                $user->setImage($FileName);
            }

            $em->flush();
            $this->addFlash('success', 'Profil modifié avec succès !');
            return $this->redirectToRoute('app_private');
        }

        return $this->render('private/user_edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/article/create', name: 'article_create')] 
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        // On récupére l'utilisateur connecté
        $user = $this->getUser();

        // On vérifie que l'utilisateur est connecté
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        
        }
        // On instancie la class Article
        $article = new Article();

        // On crée le formulaire
        $form = $this->createForm(ArticleType::class, $article);
        
        // On récupére les données du formulaire
        $form->handleRequest($request);

        // Si formulaire soummit et validé, on execute la requete auprés de la bdd
        if($form->isSubmitted() && $form->isValid()){

            $article->setUser($user);

            // Attribution de la date de création
            $article->setCreationDate(new \DateTime());


            // On récupére l'image issu du formulaire
            $imageFile = $form->get('image')->getData();

            // Si une image a bien été envoyé
            if($imageFile){
                // On donne un nom de fichier à l'image avec uniqid pour avoir un nom unique
                //      et on ajoute l'extension devinée avec la function guessExtension()
                $FileName = uniqid().'.'.$imageFile->guessExtension();
                
                // On envoi le fichier dans le dossier prévu pour les images
                $imageFile->move(
                    $this->getParameter('images_directory'),$FileName
                );

                // On enregistre le nom du fichier dans l'entité Produit
                $article->setImage($FileName);
            }

            $em->persist($article);
            $em->flush();

            // créer un message flash de validation : 'l'article a bien été crée'
            // type : succes, error, warning
            $this->addFlash('success', "l'article a bien été crée");
            return $this->redirectToRoute("app_private");
        }

        return $this->render('article/create.html.twig', [
            "form" => $form
        ]);
    }


    #[Route('/private/edit/{id}', name: 'article_edit')]
    public function edit(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        // On récupére l'utilisateur connecté
        $user = $this->getUser();

        // Si ce n'est pas l'auteur, on redirige immédiatement
        if (!$user || $article->getUser() !== $user) {
            $this->addFlash('danger', "Tu n'as pas le droit de modifier cet article.");
            return $this->redirectToRoute('app_private');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Attribution de la date de modification
            $article->setUpdateDate(new \DateTime());

            // On récupére l'image issu du formulaire
            $imageFile = $form->get('image')->getData();

            // Si une image a bien été envoyé
            if($imageFile){

                // Suppression de l’ancienne image si existante
                if ($article->getImage() && file_exists($this->getParameter('images_directory').'/'.$article->getImage())) {
                    unlink($this->getParameter('images_directory').'/'.$article->getImage());
                }

                // On donne un nom de fichier à l'image avec uniqid pour avoir un nom unique
                //      et on ajoute l'extension devinée avec la function guessExtension()
                $FileName = uniqid().'.'.$imageFile->guessExtension();
                
                // On envoi le fichier dans le dossier prévu pour les images
                $imageFile->move(
                    $this->getParameter('images_directory'),$FileName
                );

                // On enregistre le nom du fichier dans l'entité Article
                $article->setImage($FileName);
            }

            $em->flush();
            $this->addFlash('success', 'Article modifié avec succès !');
            return $this->redirectToRoute('app_private');
        }



        return $this->render('article/edit.html.twig', [
            'form' => $form,
            'article' => $article,
        ]);
    }

    #[Route('/private/delete/{id}', name: 'article_delete')]
    public function delete(Article $article, EntityManagerInterface $em): Response
    {
        // On récupére l'utilisateur connecté
        $user = $this->getUser();

        // Si ce n'est pas l'auteur, on redirige immédiatement
        if (!$user || $article->getUser() !== $user) {
            $this->addFlash('danger', "Tu n'as pas le droit de modifier cet article.");
            return $this->redirectToRoute('app_private');
        };

        // On supprime l'article en utilisant Doctrine
        $em->remove($article);
        $em->flush();

        // On envoi un message en cas de succés
        $this->addFlash('success', 'Article supprimé avec succès !');

        // On renvoi vers user_homepage.html.twig
        return $this->redirectToRoute('app_private');

    }

    #[Route('/article/{id}/like', name: 'article_like', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function like(Article $article, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($article->getLikes()->contains($user)) {
            // Déjà liké, donc on retire le like (dislike)
            $article->removeLike($user);
        } else {
            // Pas encore liké, donc on like
            $article->addLike($user);
        }

        $em->flush();

        // Redirection vers la page de l'article
        return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
    }

}
