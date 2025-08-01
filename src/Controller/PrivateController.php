<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PrivateController extends AbstractController
{
    
    #[Route('/private/article/', name: 'app_private')]
    public function index(ArticleRepository $articleRepository, Request $request): Response
    {

        // On récupére la liste de tous les articles
        $articles = $articleRepository->findAll();

        return $this->render('private/user_homepage.html.twig', [
            'controller_name' => 'ProduitController',
            'articles' => $articles,
        ]);
    }
}
