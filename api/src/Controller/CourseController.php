<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Instrument;
use App\Entity\User;
use App\Entity\Composer;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ComposerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Repository\CourseRepository;
use App\Repository\UserRepository;
use App\Repository\InstrumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;


class CourseController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/courses', name: 'course_list', methods: ['GET'])]
    public function getCourseList(CourseRepository $courseRepository, SerializerInterface $serializer): JsonResponse
    {
        $coursesList = $courseRepository->findAll();

        $serializedCourses = $serializer->serialize($coursesList, 'json', ['groups' => ['course','course_users', 'course_professor', 'course_composers', 'course_instruments', 'instrument', 'course_category', 'category', 'quizz_course', 'quizz']]);

        return new JsonResponse($serializedCourses, 200, [], true);

    }

    #[Route('/courses/{courseId}', name: 'course_by_id', methods: ['GET'])]
    public function getCoursesWithId(string $courseId, CourseRepository $courseRepository, SerializerInterface $serializer): JsonResponse
    {

        $type = (int)$courseId;

        $course = $courseRepository->find($type);

        if (!$course) {
            return new JsonResponse(['message' => 'Course not found TEST'], 404);
        }

        $serializedCourse = $serializer->serialize($course, 'json', ['groups' => ['course', 'course_users', 'course_professor', 'course_composers', 'course_comments', 'course_instruments', 'instrument', 'course_category', 'category'], 'datetime_format' => 'Y-m-d H:i:s']);

        return new JsonResponse($serializedCourse, 200, [], true);

    }

    #[Route('/courses/users/{userId}', name: 'course_list_by_user', methods: ['GET'])]
    public function getCourseListByUser(int $userId, CourseRepository $courseRepository, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        $courses = $courseRepository->findByUser($user);

        $serializedCourses = $serializer->serialize($courses, 'json', ['groups' => ['course','course_users', 'course_professor', 'course_composers', 'course_instruments', 'instrument', 'quizz_course']]);

        return new JsonResponse($serializedCourses, 200, [], true);

    }

    #[Route('/courses/professors/{profId}', name: 'course_list_by_prof', methods: ['GET'])]
    public function getCourseListByProf(int $profId, CourseRepository $courseRepository, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($profId);

        if (!$user) {
            return new JsonResponse(['message' => 'Professor not found'], 404);
        }

        $courses = $courseRepository->findByProf($user);

        $serializedCourses = $serializer->serialize($courses, 'json', ['groups' => ['course','course_users', 'course_professor', 'course_composers' ,'course_instruments' , 'instrument']]);

        return new JsonResponse($serializedCourses, 200, [], true);

    }

    #[Route('/courses/instruments/{instrumentId}', name: 'course_list_by_instrument', methods: ['GET'])]
    public function getCourseListByInstrument(int $instrumentId, CourseRepository $courseRepository, InstrumentRepository $instrumentRepository, SerializerInterface $serializer): JsonResponse
    {
        $instrument = $instrumentRepository->find($instrumentId);

        if (!$instrument) {
            return new JsonResponse(['message' => 'Instrument not found'], 404);
        }

        $courses = $courseRepository->findByInstrument($instrument);

        $serializedCourses = $serializer->serialize($courses, 'json', ['groups' => ['course','course_users', 'course_professor', 'course_composers']]);

        return new JsonResponse($serializedCourses, 200, [], true);

    }


    #[Route('/courses/category/{categoryID}', name: 'course_list_by_instrument', methods: ['GET'])]
    public function getCourseListByCategory(int $categoryID, CourseRepository $courseRepository, CategoryRepository $categoryRepository , SerializerInterface $serializer): JsonResponse
    {
        $category = $categoryRepository->find($categoryID);

        if (!$category ) {
            return new JsonResponse(['message' => 'Category not found'], 404);
        }

        $courses = $courseRepository->findByCategory($category);

        $serializedCourses = $serializer->serialize($courses, 'json', ['groups' => ['course','course_users', 'course_professor', 'course_composers']]);

        return new JsonResponse($serializedCourses, 200, [], true);

    }


    #[Route('/courses/title/{title}', name: 'course_list_by_title', methods: ['GET'])]
    public function getCourseListByTitle(string $title, CourseRepository $courseRepository, SerializerInterface $serializer): JsonResponse
    {
        $courseByTitle = $courseRepository->findByTitle($title);

        if (!$courseByTitle ) {
            return new JsonResponse(['message' => 'Category not found'], 404);
        }

        $serializedCourses = $serializer->serialize($courseByTitle, 'json', ['groups' => ['course','course_users', 'course_professor', 'course_composers']]);

        return new JsonResponse($serializedCourses, 200, [], true);

    }


    #[Route('/courses/search', name: 'course_list_by_title', methods: ['POST'])]
    public function searchCourses (  Request $request, CourseRepository $courseRepository ,ManagerRegistry $doctrine,SerializerInterface $serializer): Response {

        $data = json_decode($request->getContent(),true);
        $user = $data['professorId'];
        $instrumentName = $data['instrumentName'];
        $category = $data['categoryId'];
        $composer = $data['composerId'];
        $title = $data['title'] ? $data['title']  : '';

        $user = $user ? $doctrine->getRepository(User::class)->find($user) : null;
        $instrument = $instrumentName ? $doctrine->getRepository(Instrument::class)->findOneBy(['name' => $instrumentName]) : null;
        $category = $category ? $doctrine->getRepository(Category::class)->find($category) : null;
        $composer = $composer ? $doctrine->getRepository(Composer::class)->find($composer) : null;
        
        $results = $courseRepository->findByCriteria($user, $instrument, $category, $composer, $title);

        if (!$results ) {
            return new JsonResponse(['message' => 'Aucun cours pour ces critères'], 404);
        }
     
        $serializedCourses = $serializer->serialize($results, 'json', ['groups' => ['course', 'course_professor', 'course_category', 'category', 'course_composers' , 'course_composers' ,'course_instruments' , 'instrument']]);

        return new JsonResponse($serializedCourses, 200, [], true);
    }


    #[Route('/courses/composer/{composerID}', name: 'course_list_by_instrument', methods: ['GET'])]
    public function getCourseListBycomposer(int $composerID, CourseRepository $courseRepository, ComposerRepository $composerRepository , SerializerInterface $serializer): JsonResponse
    {
        $composer = $composerRepository->find($composerID);

        if (!$composer ) {
            return new JsonResponse(['message' => 'composer not found'], 404);
        }

        $courses = $courseRepository->findBycomposer($composer);

        $serializedCourses = $serializer->serialize($courses, 'json', ['groups' => ['course','course_users', 'course_professor', 'course_composers']]);

        return new JsonResponse($serializedCourses, 200, [], true);

    }


    #[Route('/courses-datas-creation', name: 'course_datas-creation', methods: ['GET'])]
    public function getAllCoursesDatas( SerializerInterface $serializer) {

        $instruments = $this->entityManager->getRepository(Instrument::class)->findAll();
        $composers = $this->entityManager->getRepository(Composer::class)->findAll();
        $category = $this->entityManager->getRepository(Category::class)->findAll();
        $usersList = $this->entityManager->getRepository(User::class)->findAll();
        $professorList = array();
        $datas = [] ;

        foreach ($usersList as $user) {
            if($user->getRoles()[0] == 'ROLE_PROFESSOR'){
                array_push($professorList, $user);
            }
        }
        $datas = [
            'professors' => $professorList,
            'categories' => $category,
            'composers' => $composers,
            'instruments' => $instruments
        ];

        $jsonUsersList = $serializer->serialize($datas, 'json', ['groups' => ['user' ,'course','course_users', 'course_professor', 'course_composers', 'course_instruments', 'instrument', 'course_category', 'category', 'quizz_course', 'quizz']]);
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, ['accept' => 'json'], true);

    }

    #[Route('/new-course', name: 'new_course', methods: ['POST'])]
    public function newCourse(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $instrument = $this->entityManager->getRepository(Instrument::class)->find($data['instrumentId']);
        $professor = $this->entityManager->getRepository(User::class)->find($data['professorId']);
        $category = $this->entityManager->getRepository(Category::class)->find($data['categoryId']);
        $composer = $this->entityManager->getRepository(Composer::class)->find($data['composerId']);

        if (!$instrument) {
            return new Response("Instrument not found", 404);
        }
        if (!$professor) {
            return new Response("Professor not found", 404);
        }
        if (!$category) {
            return new Response("Category not found", 404);
        }

        if (!$composer) {
            return new Response("Composer not found", 404);
        }  

        $course = new Course();
        $course->setCreatedAt(new \DateTimeImmutable());
        $course->setTitle($data['title']);
        $course->setDescription($data['description']);
        $course->setPrice($data['price']);
        $course->setLinkVideo($data['linkVideo']);
        $course->setPreview($data['preview']);
        $course->setPhoto($data['photo']);
        $course->setInstrument($instrument);
        $course->setProfessor($professor);
        $course->addComposer($composer);
        $course->addCategory($category);
        $this->entityManager->persist($course);
        $this->entityManager->flush();
        return new Response('Course created successfully', Response::HTTP_CREATED);

    }

    #[Route('/update-course/{courseId}', name: 'update_course', methods: ['PUT'])]
    public function courseUpdate(Request $request , int $courseId, CourseRepository $courseRepository,): Response
    {
        $data = json_decode($request->getContent(), true);
        $instrument = $this->entityManager->getRepository(Instrument::class)->find($data['instrumentId']);
        $professor = $this->entityManager->getRepository(User::class)->find($data['professorId']);
        $category = $this->entityManager->getRepository(Category::class)->find($data['categoryId']);
        $composer = $this->entityManager->getRepository(Composer::class)->find($data['composerId']);
        $course = $this->entityManager->getRepository(Course::class)->find($courseId);

        if (!$course) {
            return new JsonResponse(['message' => 'Course not found'], 404);
        }
        if (!$instrument) {
            return new Response("Instrument not found", 404);
        }
        if (!$professor) {
            return new Response("Professor not found", 404);
        }
        if (!$category) {
            return new Response("Category not found", 404);
        }

        if (!$composer) {
            return new Response("Composer not found", 404);
        }  

        $course->setCreatedAt(new \DateTimeImmutable());
        $course->setTitle($data['title']);
        $course->setDescription($data['description']);
        $course->setPrice($data['price']);
        $course->setLinkVideo($data['linkVideo']);
        $course->setPreview($data['preview']);
        $course->setPhoto($data['photo']);
        $course->setInstrument($instrument);
        $course->setProfessor($professor);
        $course->addComposer($composer);
        $course->addCategory($category);
        $this->entityManager->persist($course);
        $this->entityManager->flush();
        return new Response('Course created successfully', Response::HTTP_CREATED);

    }

    #[Route('/courses-delete/{courseId}', name: 'course_delete', methods: ['DELETE'])]
    public function deleteCourse(int $courseId, CourseRepository $courseRepository): Response
    {
        $course = $courseRepository->find($courseId);

        if (!$course) {
            return new JsonResponse(['message' => 'Course not found'], 404);
        }

        $courseRepository->remove($course, true);

        return new Response('Course deleted successfully', Response::HTTP_OK);
    }

    #[Route('/courses-delete-many', name: 'course_delete_many', methods: ['DELETE'])]
    public function deleteCourseMany(Request $request, CourseRepository $courseRepository): JsonResponse
    {
        $courseIds = $request->query->get('courseIds');
    
        if (!$courseIds) {
            return new JsonResponse(['message' => 'No course IDs provided'], Response::HTTP_BAD_REQUEST);
        }
    
        $courseIds = json_decode($courseIds, true);
    
        foreach ($courseIds as $courseId) {
            $course = $courseRepository->find($courseId);
    
            if ($course) {
                $this->entityManager->remove($course);
            }
        }
    
        $this->entityManager->flush();
    
        return new JsonResponse(['message' => 'Courses deleted successfully'], Response::HTTP_OK);
    }

}
