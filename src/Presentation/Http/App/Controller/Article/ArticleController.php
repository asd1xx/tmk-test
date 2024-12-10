<?php

namespace App\Presentation\Http\App\Controller\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Persistence\Doctrine\Repository\Article\ArticleRepository;
use Twig\Environment;
use App\Domain\Entity\Article\Article;
use App\Form\ArticleType;
use Carbon\Carbon;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article_index', methods: ['GET'])]
    public function index(Environment $twig, ArticleRepository $articleRepository): Response
    {
        return new Response($twig->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]));
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $createdAt = Carbon::now();
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $article->setUid(uniqid('article'));
        $article->setCreatedAt($createdAt);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Environment $twig, Article $article, EntityManagerInterface $entityManager): Response
    {
        $countViews = $article->getViews();
        $article->setViews($countViews + 1);
        $entityManager->persist($article);
        $entityManager->flush();

        return new Response($twig->render('article/show.html.twig', [
            'article' => $article,
        ]));
    }
}
