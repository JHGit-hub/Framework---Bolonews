<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\ArticleRepository;
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

        if($form->isSubmitted() && $form->isValid()){
            $searchData = $form->get('recherche')->getData();
            //Je vérifie s'il y a des infos dans la recherche
            if(empty($searchData)){
                //Afficher tous les articles
                $articles = $articleRepository->findAll();
            } else {
                //Afficher les articles correspondant à la recherche
                $produits = $articleRepository->findBySearch($searchData);
            }
        }

        return $this->render('public/index.html.twig', [
            'controller_name' => 'ProduitController',
            'articles' => $articles,
            'form' => $form->createView(),
        ]);
    }



}
