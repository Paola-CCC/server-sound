<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\User;
use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Repository\ImagesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/uploadimage')]
class UploadimageController extends AbstractController
{
    private $em; // Déclarez la variable pour stocker EntityManagerInterface

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em; // Injectez l'EntityManagerInterface dans le constructeur
    }
    #[Route('/', name: 'app_uploadimage_index', methods: ['GET'])]
    public function index(ImagesRepository $imagesRepository): JsonResponse
    {
        $images = $imagesRepository->findAll();
        $data = [];

        foreach ($images as $image) {
            $data[] = [
                'id' => $image->getId(),
                'imageName' => $image->getImageName(),
                'url' => '/images/upload/' . $image->getImageName()
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/new', name: 'app_uploadimage_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $image = new Images();
        $uploadedFile = $request->files->get('imageFile');

        if (!$uploadedFile) {
            return new JsonResponse(['errors' => 'No file uploaded'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $image->setImageFile($uploadedFile);
        $image->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($image); 
        $em->flush();

        return new JsonResponse([
            'message' => 'Image uploaded successfully',
            'id' => $image->getId(),
            'url' => '/images/upload/' . $image->getImageName()
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_uploadimage_show', methods: ['GET'])]
    public function show(Images $image): JsonResponse
    {
        return new JsonResponse([
            'id' => $image->getId(),
            'imageName' => $image->getImageName(),
            'url' => '/images/upload/' . $image->getImageName(),
            'createdAt' => $image->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $image->getUpdatedAt()?->format('Y-m-d H:i:s')
        ]);
    }
   
#[Route('/{id}', name: 'app_uploadimage_update', methods: ['POST'])]
public function update(int $id, Request $request, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $image = $imagesRepository->find($id);

    if (!$image) {
        return new JsonResponse(['errors' => 'Image not found'], JsonResponse::HTTP_NOT_FOUND);
    }

    $uploadedFile = $request->files->get('imageFile');
    
    if (!$uploadedFile) {
        return new JsonResponse(['errors' => 'No file uploaded'], JsonResponse::HTTP_BAD_REQUEST);
    }

    // Logic to handle the uploaded file using VichUploader or any other method
    $image->setImageFile($uploadedFile);
    
    $entityManager->persist($image);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Image updated successfully', 'id' => $image->getId(), 'url' => '/images/upload/' . $image->getImageName()], JsonResponse::HTTP_OK);
}
    
    #[Route('/{id}', name: 'app_uploadimage_delete', methods: ['DELETE'])]
    public function delete(Images $image, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($image);
        $em->flush();

        return new JsonResponse(['message' => 'Image deleted successfully'], JsonResponse::HTTP_OK);
    }

    #[Route('/new/{type}/{entityId}', name: 'app_uploadimage_new_for_entity', methods: ['POST'])]
    public function newForEntity(
        string $type, 
        int $entityId, 
        Request $request, 
        ImagesRepository $imageRepo,
        UserRepository $userRepo, 
        CourseRepository $courseRepo
    ): JsonResponse {
        $image = new Images();
        $uploadedFile = $request->files->get('imageFile');
    
        if (!$uploadedFile) {
            return new JsonResponse(['errors' => 'No file uploaded'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
       // Après avoir persisté l'image
    $image->setImageFile($uploadedFile);
    $image->setCreatedAt(new \DateTimeImmutable());
    $imageRepo->save($image, true);

    switch ($type) {
        case 'user':
            $user = $userRepo->find($entityId);
            if (!$user) {
                return new JsonResponse(['errors' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
            }
            $user->setImage($image);
            $userRepo->save($user);
            
            $this->em->persist($user);
            $this->em->persist($image);
            $this->em->flush();
            break;

        case 'course':
            $course = $courseRepo->find($entityId);
            if (!$course) {
                return new JsonResponse(['errors' => 'Course not found'], JsonResponse::HTTP_NOT_FOUND);
            }
            $course->setImage($image);
            $courseRepo->save($course);

            $this->em->persist($course);
            $this->em->persist($image);
            $this->em->flush();
            break;

        default:
            return new JsonResponse(['errors' => 'Invalid entity type'], JsonResponse::HTTP_BAD_REQUEST);
    }

    return new JsonResponse([
        'message' => "Image uploaded and associated with $type successfully",
        'id' => $image->getId(),
        'url' => '/images/upload/' . $image->getImageName()
    ], JsonResponse::HTTP_CREATED);
}
    
    

}
