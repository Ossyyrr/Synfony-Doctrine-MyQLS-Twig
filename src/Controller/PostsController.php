<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\PostsType;
use App\Entity\Posts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class PostsController extends AbstractController
{
    /**
     * @Route("/registrar-posts", name="registrarPosts")
     */
    public function index(Request $request)
    {
        $posts= new Posts();
        $form = $this->createForm(PostsType::class, $posts);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $brochureFile = $form['foto']->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                   throw new \Exception('Ha ocurrido un error');
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $posts->setFoto($newFilename);
            }

            $user = $this->getUser();
            $posts->setUser($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($posts);
            $em->flush();
            return $this->redirectToRoute('misPosts');
        }

        return $this->render('posts/index.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/post/{id}", name="verPost")
     */
    public function verPost($id, EntityManagerInterface $em){
        $post = $em->getRepository(Posts::class)->find($id);
        return $this->render('posts/verPost.html.twig', ['post'=>$post]);
    }

      /**
     * @Route("/mis-posts", name="misPosts")
     */
    public function MisPost(EntityManagerInterface $em){
        $user = $this->getUser();
        $posts = $em->getRepository(Posts::class)->findBy(['user'=>$user]);
        return $this->render('posts/misPosts.html.twig',['posts'=>$posts]);
    }


    /**
     * @Route("/delete-posts/{id}", name="deletePost")
     */
    public function deletePost($id, EntityManagerInterface $em){
        $post = $em->getRepository(Posts::class)->findOneById($id);
        $em->remove($post);  //expects parameter 1 to be an entity object, array given.
        $em->flush();
        return $this->redirect('/');

    }
    
    /**
     * @Route("/like/{id}", name="like")
     */
    
    public function setLike($id, EntityManagerInterface $em){
        $post = $em->getRepository(Posts::class)->findOneById($id);
        if ($post->getLikes()){
        $likes = strval($post->getLikes() +1);
    }
    else{
        $likes = '1';
    }
        $post->setLikes($likes);
        $em->persist($post);
        $em->flush();
        return $this->redirect('/');
    }
}