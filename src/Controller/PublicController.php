<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
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
    public function index(ArticleRepository $articleRepository, Request $request): Response
    {

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        // On récupére la liste de tous les articles
        $articles = $articleRepository->findAll();


        return $this->render('public/index.html.twig', [
            'controller_name' => 'ProduitController',
            'articles' => $articles,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/list', name: 'app_article')]
    public function search(CategorieRepository $categorieRepository, ArticleRepository $articleRepository, Request $request): Response
    {

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        // On récupére la liste de tous les articles
        $articles = $articleRepository->findAll();

        if($form->isSubmitted() && $form->isValid()){
            $searchData = $form->get('search')->getData();
            //Je vérifie s'il y a des infos dans la recherche
            if(empty($searchData)){
                //Afficher tous les articles
                $articles = $articleRepository->findAll();
            } else {
                //Afficher les articles correspondant à la recherche
                $articles = $articleRepository->findBySearch($searchData);
            }
        }

        // On récupére la liste de toutes les catégories
        $categories = $categorieRepository->findAll();

        return $this->render('article/list_articles.html.twig', [
            'controller_name' => 'ProduitController',
            'articles' => $articles,
            'categories' => $categories,
            'form' => $form,
        ]);
    }


    #[Route('/article/show/{id}', name: 'article_show')]
    public function showArticle(int $id, ArticleRepository $articleRepository, Request $request): Response
    {
        // On récupére le detail de l'article par son id
        $article = $articleRepository->find($id);

        return $this->render('article/show_article.html.twig',[
            'article' => $article,
        ]);

    }



}
