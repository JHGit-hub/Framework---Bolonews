<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
            if($article->isPublication()){
                $published_articles[] = $article;
            } else {
                $unpublished_articles[] = $article;
            }
        }

        return $this->render('private/user_homepage.html.twig', [
            'controller_name' => 'ProduitController',
            'articles' => $articles,
            'published_articles' => $published_articles,
            'unpublished_articles' => $unpublished_articles,
        ]);
    }

    #[Route('/private/{id}/edit', name: 'user_edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On récupére l'image issu du formulaire
            $imageFile = $form->get('photo')->getData();

            // Si une image a bien été envoyé
            if($imageFile){

                // Suppression de l’ancienne image si existante
                if ($produit->getPhoto() && file_exists($this->getParameter('images_directory').'/'.$produit->getPhoto())) {
                    unlink($this->getParameter('images_directory').'/'.$produit->getPhoto());
                }

                // On donne un nom de fichier à l'image avec uniqid pour avoir un nom unique
                //      et on ajoute l'extension devinée avec la function guessExtension()
                $FileName = uniqid().'.'.$imageFile->guessExtension();
                
                // On envoi le fichier dans le dossier prévu pour les images
                $imageFile->move(
                    $this->getParameter('images_directory'),$FileName
                );

                // On enregistre le nom du fichier dans l'entité Produit
                $produit->setPhoto($FileName);
            }

            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('produit_show', ['id' => $produit->getId()]);
        }

        return $this->render('produit/edit.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }





}
