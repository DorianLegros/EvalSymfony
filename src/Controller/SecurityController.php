<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * @Route("/token", name="gettoken", methods={"GET"})
     * @throws \Exception
     */
    public function getToken(): Response
    {
        $response = new Response();
        try {
            $token = random_bytes(100);
            $response->setStatusCode(Response::HTTP_OK);
            $response->setContent(bin2hex($token));
        } catch (\Exception $exception) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->setContent($exception);
        }

        return $response;
    }
}