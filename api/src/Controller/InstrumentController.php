<?php

namespace App\Controller;

use App\Entity\Instrument;
use App\Entity\Course;
use App\Entity\User;
use App\Entity\Composer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Repository\InstrumentRepository;


class InstrumentController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/instruments', name: 'instrument_list', methods: ['GET'])]
    public function getInstrumentList(InstrumentRepository $instrumentRepository, SerializerInterface $serializer): JsonResponse
    {
        $instrumentsList = $instrumentRepository->findAll();

        $serializedInstruments = $serializer->serialize($instrumentsList, 'json', ['groups' => ['instrument',
        'instruments_courses', 'course',
        'instruments_composers', 
        'user', 'instruments_users'
        ]]);

        return new JsonResponse($serializedInstruments, 200, [], true);
    }

    #[Route('/instruments/{instrumentId}', name: 'instrument_by_id', methods: ['GET'])]
    public function getInstrumentById(int $instrumentId, InstrumentRepository $instrumentRepository, SerializerInterface $serializer): JsonResponse
    {
        $instrument = $instrumentRepository->find($instrumentId);

        if (!$instrument) {
            return new JsonResponse(['message' => 'Instrument not found'], 404);
        }

        $serializedInstrument = $serializer->serialize($instrument, 'json', ['groups' => ['instrument', 'instruments_courses', 'course',
        'instruments_composers',
        'user', 'instruments_users'
        ]]);

        return new JsonResponse($serializedInstrument, 200, [], true);
    }

    #[Route('/api/new-instrument', name: 'new_instrument', methods: ['POST'])]
    public function newInstrument(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $instrument = new Instrument();
        $instrument->setName($data['name']);

        $this->entityManager->persist($instrument);
        $this->entityManager->flush();

        return new Response('Instrument created successfully', Response::HTTP_CREATED);
    }

    #[Route('/instruments/{instrumentId}', name: 'instrument_update', methods: ['PUT'])]
    public function updateInstrument(int $instrumentId, InstrumentRepository $instrumentRepository, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $instrument = $instrumentRepository->find($instrumentId);

        if (!$instrument) {
            return new JsonResponse(['message' => 'Instrument not found'], 404);
        }

        $instrument->setName($data['name']);
  
        $this->entityManager->persist($instrument);
        $this->entityManager->flush();

        return new Response('Instrument updated successfully', Response::HTTP_OK);
    }

    #[Route('/instruments/{instrumentId}', name: 'instrument_delete', methods: ['DELETE'])]
    public function deleteInstrument(int $instrumentId, InstrumentRepository $instrumentRepository, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $instrument = $instrumentRepository->find($instrumentId);

        if (!$instrument) {
            return new JsonResponse(['message' => 'Instrument not found'], 404);
        }

        $this->entityManager->remove($instrument);
        $this->entityManager->flush();

        return new Response('Instrument deleted successfully', Response::HTTP_OK);
    }

    #[Route('/instruments-delete-many', name: 'instruments_delete_many', methods: ['DELETE'])]
    public function deleteCourseMany(Request $request, InstrumentRepository $instrumentRepository): JsonResponse
    {
        $instrumentsIDs = $request->query->get('instrumentsIDs');
    
        if (!$instrumentsIDs) {
            return new JsonResponse(['message' => 'No course IDs provided'], Response::HTTP_BAD_REQUEST);
        }
    
        $instrumentsIDs = json_decode($instrumentsIDs, true);
    
        foreach ($instrumentsIDs as $instrumentsIds) {
            $instrument = $instrumentRepository->find($instrumentsIds);
    
            if ($instrument) {
                $this->entityManager->remove($instrument);
            }
        }
    
        $this->entityManager->flush();
    
        return new JsonResponse(['message' => 'Instruments deleted successfully'], Response::HTTP_OK);
    }

}
