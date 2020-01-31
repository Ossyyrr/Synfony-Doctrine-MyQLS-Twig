<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Posts;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index(EntityManagerInterface $em, paginatorInterface $paginator, Request $request)
    {
        $query = $em->getRepository(Posts::class)->BuscarTodosLosPosts();
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
        6 /*limit per page*/);

        

        return $this->render('dashboard/index.html.twig', [
            'posts' => $pagination,
     //       'comentarios' => $queryCom
        ]);

    }
}
