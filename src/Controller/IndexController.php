<?php 
namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request; // Ajout de l'import correct pour Request
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType; // Ajout des types de formulaire
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IndexController extends AbstractController 
{ 
    /**
     * @Route("/article/save", name="article_save")
     */
    public function save(EntityManagerInterface $entityManager) {
        // Création d'un nouvel article
        $article = new Article();
        $article->setNom('Article 1');
        $article->setPrix(1000);
    
        // Enregistrement de l'article dans la base de données
        $entityManager->persist($article);
        $entityManager->flush();
    
        return new Response('Article enregistré avec ID '.$article->getId());
    }

    /**
     * @Route("/", name="article_list")
     */
    public function home(EntityManagerInterface $entityManager) 
    { 
        // Récupérer tous les articles de la base de données
        $articles = $entityManager->getRepository(Article::class)->findAll();
        
        return $this->render('articles/index.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route("/article/new", name="new_article", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager) {
        $article = new Article();
        $form = $this->createFormBuilder($article)
            ->add('nom', TextType::class)
            ->add('prix', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Créer'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/new.html.twig', ['form' => $form->createView()]);
    }

    /**
 * @Route("/article/{id}", name="article_show")
 */
public function show($id, EntityManagerInterface $entityManager) {
    $article = $entityManager->getRepository(Article::class)->find($id);

    return $this->render('articles/show.html.twig', [
        'article' => $article
    ]);
}

/**
 * @Route("/article/edit/{id}", name="edit_article", methods={"GET", "POST"})
 */
public function edit(Request $request, EntityManagerInterface $entityManager, $id) {
    $article = $entityManager->getRepository(Article::class)->find($id);

    $form = $this->createFormBuilder($article)
        ->add('nom', TextType::class)
        ->add('prix', TextType::class)
        ->add('save', SubmitType::class, [
            'label' => 'Modifier'
        ])->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('article_list');
    }

    return $this->render('articles/edit.html.twig', [
        'form' => $form->createView()
    ]);
}


/**
 * @Route("/article/delete/{id}", name="delete_article", methods={"DELETE"})
 */
public function delete(Request $request, EntityManagerInterface $entityManager, $id) {
    $article = $entityManager->getRepository(Article::class)->find($id);

    if ($article) {
        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }
    }

    return $this->redirectToRoute('article_list');
}




}
