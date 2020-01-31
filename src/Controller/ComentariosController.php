<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ComentariosType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comentarios;
use App\Entity\Posts;
use App\Controller\PostsController;


class ComentariosController extends AbstractController
{
    /**
     * @Route("/registrar-comentarios/{id}", name="RegistrarComentarios")
     */
    public function index(Request $request, EntityManagerInterface $em, $id)
    {
        $post = $em->getRepository(Posts::class)->find($id); 
        $comentarios = $em->getRepository(Comentarios::class)->findBy(['posts' => $id]); 
        $comentario = new Comentarios();
        $form = $this->createForm(ComentariosType::class, $comentario);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $user = $this->getUser();
            $comentario->setUser($user);
            $comentario->setPosts($post);
            $em->persist($comentario);
            $em->flush();
            return $this->redirect($request->getUri());
            // return $this->redirectToRoute('dashboard');
            
        }
        return $this->render('comentarios/index.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
            'comentarios' => $comentarios
        ]);
    }

  

}
