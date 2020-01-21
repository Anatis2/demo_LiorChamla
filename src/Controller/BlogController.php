<?php

namespace App\Controller;

use App\Form\ArticleType;
use App\Form\CommentType;
use App\Entity\Article;
use App\Entity\Comment;
use App\Repository\ArticleRepository;

use Doctrine\Persistence\ObjectManager;
use \Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class BlogController extends AbstractController
{

    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo)
    {
    	$articles = $repo->findAll();

        return $this->render('blog/index.html.twig', [
            'title' => 'Blog',
			'articles' => $articles
        ]);
    }


	/**
	 * @Route("/", name="home")
	 */
    public function home()
	{
		return $this->render('blog/home.html.twig', [
			'title' => 'Accueil'
		]);
	}


	/**
	 * @Route("blog/new", name="blog_create")
	 * @Route("blog/{id}/edit", name="blog_edit")
	 */
	public function form(Article $article = null, Request $request, EntityManagerInterface $manager)
	{
		if(!$article) {
			$article = new Article();
		}

		$form = $this->createForm(ArticleType::class, $article);

	 	$form->handleRequest($request);

	 	if($form->isSubmitted() && $form->isValid()) {
	 		if(!$article->getId()) {
				$article->setCreatedAt(new \Datetime());
			}

	 		$manager->persist($article);
	 		$manager->flush();

	 		return $this->redirectToRoute('blog_show', [
				'title' => 'Article',
	 			'id' => $article->getId()
			]);
	 	}

		return $this->render('blog/create.html.twig', [
			'title' => 'Création d\'un article',
			'formArticle' => $form->createView(),
			'editMode' => $article->getId() !== null
		]);
	}


	/**
	 * @Route("/blog/{id}", name="blog_show")
	 */
	public function show(Article $article, Request $request, EntityManagerInterface $manager)
	{
		$comment = new Comment();
		$form = $this->createForm(CommentType::class, $comment);

		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid()) {

			$comment->setCreatedAt(new \Datetime())
					->setArticle($article);

			$manager->persist($comment);
			$manager->flush();

			return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
		}

		return $this->render('blog/show.html.twig', [
			'title' => 'Article',
			'article' => $article,
			'commentForm' => $form->createView()
		]);
	}


}
