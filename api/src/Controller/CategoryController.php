<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

#[Route('/category')]
class CategoryController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/all', name: 'app_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, SerializerInterface $serializer): JsonResponse
    {
        $categories = $categoryRepository->findAll();
        $data = $serializer->serialize($categories, 'json', ['groups' => 'category']);
    
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/new', name: 'app_category_new', methods: ['POST'])]
    public function new(Request $request, CategoryRepository $categoryRepository, SerializerInterface $serializer): Response
    {
        $data = $request->getContent();
        $category = $serializer->deserialize($data, Category::class, 'json');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $data = $serializer->serialize($category, 'json');

        return new Response('Category created successfully', Response::HTTP_CREATED);

    }

    #[Route('/delete-many', name: 'category_delete_many', methods: ['DELETE'])]
    public function deleteCourseMany(Request $request, CategoryRepository $categoryRepository): JsonResponse
    {
        $composersIDs = $request->query->get('categoriesIDs');
    
        if (!$composersIDs) {
            return new JsonResponse(['message' => 'No course IDs provided'], Response::HTTP_BAD_REQUEST);
        }
    
        $composersIDs = json_decode($composersIDs, true);
    
        foreach ($composersIDs as $courseId) {
            $course = $categoryRepository->find($courseId);
    
            if ($course) {
                $this->entityManager->remove($course);
            }
        }
    
        $this->entityManager->flush();
    
        return new JsonResponse(['message' => 'Courses deleted successfully'], Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(int $id, CategoryRepository $categoryRepository, SerializerInterface $serializer): JsonResponse
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            return new JsonResponse(['message' => 'Category not found'], 404);
        }

        //$serializedCategory = $serializer->serialize($category, 'json', ['groups' => ['category', 'category_course', 'category_forum', 'course', 'forum']]);
        $serializedCategory = $serializer->serialize($category, 'json', ['groups' => ['category', 'category_course', 'category_forum', 'course']]);

        return new JsonResponse($serializedCategory, 200, [], true);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['PUT'])]
    public function edit(Request $request, Category $category, SerializerInterface $serializer): Response
    {
        $data = $request->getContent();
        $categoryUpdate = $serializer->deserialize($data, Category::class, 'json');
    
        $category->setName($categoryUpdate->getName());
    
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    
        return new Response('Course updated successfully', Response::HTTP_OK);
    }

#[Route('/{id}', name: 'app_category_delete', methods: ['DELETE'])]
public function delete(Category $category): JsonResponse
{
    $this->entityManager->remove($category);
    $this->entityManager->flush();

    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}
}