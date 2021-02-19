<?php


namespace App\Controller;

use App\Entity\Media;
use App\Repository\MediaRepository;
use DateTime;
use mysql_xdevapi\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MediaController extends AbstractController
{
    public function serializeMedia($media) {
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return $serializer->serialize($media, 'json');
    }

    /**
     * @Route("/medias", name="getmedias", methods={"GET"})
     * @return JsonResponse
     */
    public function getMedias(): JsonResponse {
        $response = new JsonResponse();
        try {
            $em = $this->getDoctrine()->getManager();
            $medias = $em->getRepository(Media::class)->findAll();

            if (empty($medias)) {
                $response->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
                $response->setContent("No medias found in the database.");
                return $response;
            }

            $response->setContent($this->serializeMedia($medias));
            $response->setStatusCode(JsonResponse::HTTP_OK);
            return $response;
        } catch (\Exception $exception) {
            $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            $response->setContent($exception->getMessage());
            return $response;
        }
    }

    /**
     * @Route("/medias/{id}", name="getmediabyid", methods={"GET"})
     * @param int $id
     * @return JsonResponse
     */
    public function getMediaById(int $id): JsonResponse {
        $response = new JsonResponse();
        try {
            $em = $this->getDoctrine()->getManager();
            $media = $em->getRepository(Media::class)->find($id);

            if (empty($media)) {
                $response->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
                $response->setContent("No media found with this ID in the database.");
                return $response;
            }

            $response->setContent($this->serializeMedia($media));
            $response->setStatusCode(JsonResponse::HTTP_OK);
            return $response;
        } catch (\Exception $exception) {
            $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            $response->setContent($exception->getMessage());
            return $response;
        }
    }

    /**
     * @Route("/medias/{token}", defaults={"token"="none"}, name="createmedia", methods={"POST"})
     * @param Request $request
     * @param string $token
     * @return Response
     */
    public function createMedia(Request $request, string $token): Response
    {
        $response = new Response();
        try {
            if(empty($token) || $token == "none") {
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                $response->setContent("Bad request: a token is required.");
                return $response;
            } else {
                if (strlen($token) != 200) {
                    $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                    $response->setContent("Bad request: your token is not valid");
                    return $response;
                }
            }
            $media = new Media();
            $reqData = json_decode($request->getContent(), true);
            $existingMedia = $this->getDoctrine()->getManager()->getRepository(Media::class)->findBy(["Name" => $reqData["Name"], "Date" => DateTime::createFromFormat("d/m/Y H:i:s", $reqData["Date"] . " 00:00:00")]);
            if (!empty($existingMedia)) {
                $response->setStatusCode(Response::HTTP_CONFLICT);
                $response->setContent("Conflict: A media with the same date and name already exist");
                return $response;
            }
            $mediaDate = DateTime::createFromFormat("d/m/Y H:i:s", $reqData["Date"] . " 00:00:00");
            $media->setName($reqData["Name"])
                ->setType($reqData["Type"])
                ->setDate($mediaDate)
                ->setSynopsis($reqData["Synopsis"]);

            $em = $this->getDoctrine()->getManager();
            $em->persist($media);
            $em->flush();
            $response->setContent("Media has been successfully created with ID: " . $media->getId());
            $response->setStatusCode(Response::HTTP_CREATED);
            return $response;
        } catch (\Exception $exception) {
            $response->setContent($exception->getMessage());
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
    }
}