<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\SearchType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PublicController extends AbstractController
{
    /*
    #[Route('/', name: 'app_public')]
    public function index(): Response
    {
        return $this->render('public/index.html.twig', [
            'controller_name' => 'PublicController',
        ]);
    }
*/

    #[Route('/', name: 'app_public')]
    public function index(CategorieRepository $categorieRepository, ArticleRepository $articleRepository): Response
    {
        // On récupére la liste de tous les articles
        $articles = $articleRepository->findAll();

        // On récupére la liste de toutes les catégories
        $categories = $categorieRepository->findAll();


        return $this->render('public/index.html.twig', [
            'controller_name' => 'ProduitController',
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    #[Route('/article/list/{categorieId}', name: 'app_article', defaults: ['categorieId' => null])]
    public function search($categorieId = null, CategorieRepository $categorieRepository, ArticleRepository $articleRepository, Request $request): Response
    {

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        // On récupére la liste de tous les articles
        $articles = $articleRepository->findAll();

        // On récupére la liste de toutes les catégories
        $categories = $categorieRepository->findAll();

        // Si le formulaire est rempli, on filtre sur la recherche des contenus, des chapeaux ou des titres
        if($form->isSubmitted() && $form->isValid()){
            $searchData = $form->get('search')->getData();
            //Je vérifie s'il y a des infos dans la recherche
            if(!empty($searchData)){
                //Afficher les articles correspondant à la recherche
                $articles = $articleRepository->findBySearch($searchData);
            }
        // Si une catégorie est choisie (via l'URL)
        } elseif ($categorieId !== null) {
            $articles = $articleRepository->findBy(['categorie' => $categorieId]);
        }

        return $this->render('article/list_articles.html.twig', [
            'controller_name' => 'ProduitController',
            'articles' => $articles,
            'categories' => $categories,
            'form' => $form,
        ]);
    }


    #[Route('/article/show/{id}', name: 'article_show')]
    public function showArticle(
    int $id, 
    ArticleRepository $articleRepository,
     EntityManagerInterface $em, 
    Request $request): Response
    {

        // On récupére le detail de l'article par son id
        $article = $articleRepository->find($id);

        // On récupére l'utilisateur connecté
        $user = $this->getUser();

        // On instancie la classe comment
        $comment = new Comment();

        // création du formulaire de commentaire
        $form = $this->createForm(CommentType::class, $comment);

        // récupération des données du formulaire
        $form->handleRequest($request);

        // Si le formulaire est rempli, valider et on a un utilisateur connecté
        if($user && $form->isSubmitted() && $form->isValid()){

            $comment->setUser($user);
            if ($id instanceof \App\Entity\Article) {
                $comment->setArticle($id);
            }

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('article_show', ['id' => $id]);

        }

        // On récupére la liste des commentaires de l'article
        $comments = $article->getComments();

        return $this->render('article/show_article.html.twig',[
            'article' => $article,
            'form' => $form,
            'comments' => $comments,
        ]);

    }



}
