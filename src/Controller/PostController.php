<?php

namespace App\Controller;


use App\Entity\Post;
use App\Entity\Rubrik;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PostController extends AbstractController
{
    private $repo;
    private $emi;
    public function __construct(PostRepository $repo, EntityManagerInterface $emi){
        $this->repo = $repo;
        $this->emi = $emi;
    }
    //GESTION DE L'AFFICHAGE DE LA PAGE D'ACCEUIL(INDEX)
    #[Route('/', name: 'app_post')]
    public function index(): Response
    {
        $posts = $this->repo->findBy([],['createdAt'=>'DESC'],1);
        $posts2 = $this->repo->findBy([],['createdAt' => 'DESC'],4,1);
        $comments = new Comment();

        return $this->render('post/index.html.twig',[
            'posts' => $posts, 'posts2' => $posts2,
        ]);
    }

    //GESTION DE LA RECUPERATION DU DETAIL D'UN POST(TOTALITE D'UN POST)

     //GESTION DE LA RECUPERATION DU DETAIL D'UN POST(TOTALITE D'UN POST)
   
    // src/Controller/PostController.php
    #[IsGranted('ROLE_USER')]
    #[Route('/post/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function showone(Post $post, Request $req, $id, PostRepository $reppo, EntityManagerInterface $emi, CommentRepository $crepo): Response
    {
        // Vérification du post
        if (!$post) {
            return $this->redirectToRoute('app_post');
        }

        $comments = new Comment();
        $posts = $reppo->find($id);
        $posts2 = $this->repo->findBy([],['createdAt' => 'DESC'],2,1);
        // Créer le formulaire
        $commentForm = $this->createForm(CommentFormType::class, $comments);
        $commentForm->handleRequest($req);
        // Traitement du formulaire de commentaire
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $user = $this->getUser();
            $comments->setUser($user);
            $comments->setPost($posts);
            $comments->setCreatedAt(new \DateTimeImmutable());

            // Persister le commentaire
            $emi->persist($comments);
            $emi->flush();
            // Rediriger pour éviter la resoumission du formulaire
            return $this->redirectToRoute('show', ['id' => $id]);
        }
        // Récupération des commentaires pour le post
        $allComments = $crepo->findByPostOrderedByCreatedAtDesc($id);

        // Rendre la vue avec les données appropriées
        return $this->render('show/show.html.twig', [
            'post' => $post,
            'posts2' => $posts2,
            'comments' => $allComments,
            'comment_form' => $commentForm->createView()
        ]);
        
    }

   //GESTION DES RUBRIKS
   #[Route('/rubrik/rubrik/{id}', name: 'posts_by_rubrik')]
   public function postsByRubrik(Rubrik $rubrik, PostRepository $postRepository): Response
   {
       $posts = $postRepository->findByRubrik($rubrik);

       return $this->render('rubrik/rubrik.html.twig', [
           'rubrik' => $rubrik,
           'posts' => $posts,
       ]);
   }
   
   #[Route('/favoris/ajout/{id}', name: 'ajout_favoris')]
   public function ajoutFavoris(Post $post, EntityManagerInterface $emi): Response
   {

        $post->addFavori($this->getUser());


        $emi->persist($post);
        $emi->flush();

       return $this->redirectToRoute('app_post');
   }

   #[Route('/favoris/retrait/{id}', name: 'retrait_favoris')]
   public function retraiFavoris(Post $post, EntityManagerInterface $emi): Response
   {

        $post->removeFavori($this->getUser());


        $emi->persist($post);
        $emi->flush();

       return $this->redirectToRoute('app_post');
   }

    
}
