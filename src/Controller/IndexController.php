<?php 
namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\PriceSearch; // Make sure to import PriceSearch
use App\Form\PriceSearchType; 
use App\Entity\CategorySearch; 
use App\Form\CategorySearchType; 

class IndexController extends AbstractController 
{
    #[Route('/article/save', name: 'article_save')]
    public function save(EntityManagerInterface $entityManager): Response {
        // Create a new article
        $article = new Article();
        $article->setNom('Article 1');
        $article->setPrix(1000);

        // Save the article in the database
        $entityManager->persist($article);
        $entityManager->flush();

        return new Response('Article saved with ID ' . $article->getId());
    }

    #[Route('/', name: 'article_list')]
    public function home(Request $request, EntityManagerInterface $entityManager): Response
    {
        $propertySearch = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class, $propertySearch);
        $form->handleRequest($request);

        $articles = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $nom = $propertySearch->getNom();
            $articleRepository = $entityManager->getRepository(Article::class);
            $articles = $nom ? $articleRepository->findBy(['nom' => $nom]) : $articleRepository->findAll();
        }

        return $this->render('articles/index.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }

    #[Route('/article/new', name: 'new_article', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response { 
        $article = new Article(); 
        $form = $this->createForm(ArticleType::class, $article); 
        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) { 
            $entityManager->persist($article); 
            $entityManager->flush(); 

            return $this->redirectToRoute('article_list'); 
        }

        return $this->render('articles/new.html.twig', ['form' => $form->createView()]); 
    }

    #[Route('/article/edit/{id}', name: 'edit_article', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response { 
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found.');
        }

        $form = $this->createForm(ArticleType::class, $article); 
        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) { 
            $entityManager->flush(); 
            return $this->redirectToRoute('article_list'); 
        } 

        return $this->render('articles/edit.html.twig', ['form' => $form->createView()]); 
    }

    #[Route('/article/{id}', name: 'article_show')]
    public function show(int $id, EntityManagerInterface $entityManager): Response {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found.');
        }

        return $this->render('articles/show.html.twig', ['article' => $article]);
    }

    #[Route('/article/delete/{id}', name: 'delete_article', methods: ['DELETE'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, int $id): Response {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found.');
        }

        // Check CSRF token validity
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_list');
    }

    #[Route('/category/newCat', name: 'new_category')]
    public function newCategory(Request $request, EntityManagerInterface $entityManager): Response {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('article_list');  // Redirect to the article list route
        }

        return $this->render('articles/newCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/art_cat', name: 'article_par_cat')]
    public function articlesParCategorie(Request $request, EntityManagerInterface $entityManager) { 
        $categorySearch = new CategorySearch(); 
        $form = $this->createForm(CategorySearchType::class, $categorySearch); 
        $form->handleRequest($request); 

        $articles = []; 
        if ($form->isSubmitted() && $form->isValid()) { 
            $category = $categorySearch->getCategory(); 
            if ($category) { 
                $articles = $category->getArticles(); 
            } else { 
                $articles = $entityManager->getRepository(Article::class)->findAll(); 
            } 
        }

        return $this->render('articles/articlesParCategorie.html.twig', [
            'form' => $form->createView(), 
            'articles' => $articles
        ]); 
    }

    #[Route('/art_prix', name: 'article_par_prix')]
    public function articlesParPrix(Request $request, EntityManagerInterface $entityManager) {
        $priceSearch = new PriceSearch();
        $form = $this->createForm(PriceSearchType::class, $priceSearch);
        $form->handleRequest($request);
        
        $articles = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $minPrice = $priceSearch->getMinPrice();
            $maxPrice = $priceSearch->getMaxPrice();
            $articles = $entityManager->getRepository(Article::class)->findByPriceRange($minPrice, $maxPrice);
        }

        return $this->render('articles/articlesParPrix.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }
}
