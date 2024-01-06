<?php
namespace App\Controller;

use App\Entity\Composer;
use App\Entity\Instrument;
use App\Repository\ComposerRepository;
use App\Repository\InstrumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;


#[Route('/composer')]
class ComposerController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/all', name: 'app_composer_index', methods: ['GET'])]
    public function index(ComposerRepository $composerRepository, SerializerInterface $serializer): JsonResponse
    {
        $composers = $composerRepository->findAll();
    
        // Serializer
        $context = [
            'groups' => ['composer'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ];
        $json = $serializer->serialize($composers, 'json', $context);
    
        // Decode JSON and format it with JSON_PRETTY_PRINT
        $decodedJson = json_decode($json);
        $formattedJson = json_encode($decodedJson, JSON_PRETTY_PRINT);
    
        return new JsonResponse($formattedJson, Response::HTTP_OK, [], true);
    }

    #[Route('/new', name: 'app_composer_new', methods: ['POST'])]
    public function new(Request $request, ComposerRepository $composerRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $instrument = $this->entityManager->getRepository(Instrument::class)->find($data['instrumentId']);

        if (!$instrument) {
            return new JsonResponse(['message' => 'Instrument not found'], 404);
        }
        
        $composer = new Composer();
        $composer->setFullName($data['fullName']);
        $composer->setBiography($data['biography']);
        $composer->addInstrument($instrument);

        // Enregistrer le compositeur en base de données
        $this->entityManager->persist($composer);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Composer created'], Response::HTTP_CREATED);
    }



    #[Route('/delete-many', name: 'composer_delete_many', methods: ['DELETE'])]
    public function deleteCourseMany(Request $request, ComposerRepository $composerRepository): JsonResponse
    {
        $composersIDs = $request->query->get('composersIDs');
    
        if (!$composersIDs) {
            return new JsonResponse(['message' => 'No course IDs provided'], Response::HTTP_BAD_REQUEST);
        }
    
        $composersIDs = json_decode($composersIDs, true);
    
        foreach ($composersIDs as $courseId) {
            $course = $composerRepository->find($courseId);
    
            if ($course) {
                $this->entityManager->remove($course);
            }
        }
    
        $this->entityManager->flush();
    
        return new JsonResponse(['message' => 'Courses deleted successfully'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_composer_show', methods: ['GET'])]
    public function show(int $id, ComposerRepository $composerRepository, SerializerInterface $serializer): JsonResponse
    {
        $composer = $composerRepository->find($id);

        if (!$composer) {
            return new JsonResponse(['message' => 'Composer not found'], 404);
        }

        $serializedComposer = $serializer->serialize($composer, 'json', ['groups' => ['composer', 'instrument_composer']]);
        
        return new JsonResponse($serializedComposer, 200, [], true);
    }

    #[Route('/{id}/edit', name: 'app_composer_edit', methods: ['PUT'])]
    public function edit(string $id  ,   Request $request, Composer $composer, ComposerRepository $composerRepository, InstrumentRepository $instrumentRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $instrument = $this->entityManager->getRepository(Instrument::class)->find($data['instrumentId']);
        $composer = $this->entityManager->getRepository(Composer::class)->find($data['id']);

        if (!$composer) {
            return new Response("Composer not found", 404);
        }  

        $composer = new Composer();
        $composer->setFullName($data['fullName']);
        $composer->setBiography($data['biography']);
        $composer->addInstrument($instrument);

        // Enregistrer le compositeur en base de données
        $this->entityManager->persist($composer);
        $this->entityManager->flush();

        
        return new JsonResponse(['message' => 'Courses updated successfully'], Response::HTTP_OK);



    }
    #[Route('/{id}', name: 'app_composer_delete', methods: ['DELETE'])]
    public function delete(Composer $composer, ComposerRepository $composerRepository): JsonResponse
    {
        // Delete the composer
        $composerRepository->remove($composer, true);

        return new JsonResponse(['message' => 'Composer deleted'], Response::HTTP_OK);
    }
}


