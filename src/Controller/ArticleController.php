<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ArticleController extends AbstractController
{

    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article/creer', name: 'creer_article')]

    public function create(EntityManagerInterface $entityManager, 
    Request $request,
    SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/images')] string $imagesDirectory
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move($imagesDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $article->setImage($newFilename);
            }

            $article = $form->getData();
            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash('success', 'Article Created! Knowledge is power!');

            return $this->redirectToRoute('show_article');
        }
        
        return $this->render('article/creer.html.twig', [
            'controller_name' => 'ArticleController',
            'titre' => 'Article',
            'article' => $article,
            'form' => $form
        ]);
    }

    #[Route('/article/liste', name: 'show_article')]
    public function show(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('article/liste.html.twig', [
            'articles' => $articles
        ]);
    }

    #[Route('/article/update/{id}', name: 'update_article')]
    public function update(EntityManagerInterface $entityManager, 
    int $id, Request $request,
    SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/images')] string $imagesDirectory
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$id) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $article = $entityManager->getRepository(Article::class)->find($id);
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        $imageFile = $form->get('image')->getData();

        if (!$article) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $imageFile->move($imagesDirectory, $newFilename);
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'imageFilename' property to store the PDF file name
            // instead of its contents
            $article->setImage($newFilename);
        }

        

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash('success', 'Article Updated! Knowledge is power!');

            return $this->redirectToRoute('show_article');
        }

        return $this->render('article/creer.html.twig', [
            'controller_name' => 'ArticleController',
            'titre' => 'Article',
            'article' => $article,
            'form' => $form->createView()
        ]);
    }

    #[Route('/article/delete/{id}', name: 'delete_article')]

    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $article = $entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('show_article', [
            'id' => $article->getId()
        ]);
    }
}

