<?php

namespace App\Controller;

use App\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home", methods={"GET"})
     */
    public function index(): Response
    {
        $medias = $this->getDoctrine()->getRepository(Media::class)->findAll();
        return $this->render('base.html.twig', [
            'controller_name' => 'HomeController',
            'medias' => $medias
        ]);
    }
}