<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Progression;
use App\Entity\User;
use App\Repository\ProgressionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use IntlDateFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;



class ProgressionController extends AbstractController
{

    private $serializer;
    private $formatter;
    private $status = ['NOT_STARTED','IN_PROGRESS','FINISHED'];
    private $quizzStatus = ['HIDDEN','VISIBLE'];

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        $this->formatter->setPattern('dd/MM/yyyy HH:mm');
    }

    #[Route('/all-progression', name: 'app_progression', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $allProgression = $doctrine
            ->getRepository(Progression::class)
            ->findAll();

        $data = [];

        foreach ($allProgression as $progression) {
            $data[] = [
                'progressionId' => $progression->getId(),
                'user' => $progression->getUser(),
                'course' => $progression->getCourse(),
                'percentageWatched' => $progression->getPercentageWatched(),
                'videoTimestamp' => $progression->getVideoTimestamp(),
                'courseStatus' => $progression->getStatus(),
                'quizzStatus' => $progression->getQuizzStatus(),
                'creatAt' =>  $this->formatter->format($progression->getCreatedAt()),
                'updateAt' =>  $this->formatter->format($progression->getUpdateAt()),
            ];
        }
        $json = $this->serializer->serialize($data, 'json', ['groups' => [ 'course','course_professor', 'user','messages', 'progression' , 'course_composers' ], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }


    #[Route('/progression-prof/{Id}', name: 'app_progression_prof', methods: ['POST' ,'GET'])]
    public function getProgressionForProfessor (ManagerRegistry $doctrine , HttpFoundationRequest $request, EntityManagerInterface $entityManager, int $Id): JsonResponse
    {

        $data = json_decode($request->getContent(),true);
        $professorId = $doctrine
            ->getRepository(User::class)
            ->find($data['professorId']);

        if (!$professorId) {
            $textError = "Attention, l'utilisateur n'existe pas";
            return new JsonResponse($textError, 404,[], true);
        }

        $allProgression = $entityManager
            ->getRepository(Progression::class)
            ->findByProgressionProf($data['professorId']);

        $json = $this->serializer->serialize($allProgression, 'json', ['groups' => [ 'course','course_professor', 'messages', 'progression' , 'course_composers'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/progression-student/{Id}', name: 'app_progression_students', methods: ['GET'])]
    public function getProgressionForStudents (ManagerRegistry $doctrine , HttpFoundationRequest $request, EntityManagerInterface $entityManager, int $Id): JsonResponse
    {

        $studentsId = $doctrine
            ->getRepository(User::class)
            ->find($Id);

        if (!$studentsId) {
            $textError = "Attention, l'utilisateur n'existe pas";
            return new JsonResponse($textError, 404,[], true);
        }

        $allProgression = $entityManager
            ->getRepository(Progression::class)
            ->findByProgressionStudents($Id);


        $result= [] ;

        if(empty($allProgression)){
            $result[] = [
                'user' => $studentsId
            ];
            
            $json = $this->serializer->serialize($result, 'json', ['groups' => [ 'course','course_professor', 'messages',  'progression' , 'course_composers','course_category','course_instruments'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
            return new JsonResponse($json, 200, [], true);
        } else {

            $json = $this->serializer->serialize($allProgression, 'json', ['groups' => [ 'course','course_professor', 'messages',  'progression' , 'course_composers','course_category','course_instruments'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
            return new JsonResponse($json, 200, [], true);

        }
    }


    #[Route('/progression/student/search', name: 'progression_list_by_title', methods: ['POST'])]
    public function searchProgression (  Request $request, ProgressionRepository $progressionRepository, ManagerRegistry $doctrine,SerializerInterface $serializer): JsonResponse
    {

        $data = json_decode($request->getContent(),true);
        $user = $data['professorId'] && $data['professorId'] !==  "" ? $data['professorId'] : null ;
        $title = $data['title'] &&  $data['title'] !==  "" ? $data['title'] : null ;
        $status = $data['status']&&  $data['status'] !==  "" ? $data['status'] : null ;

        $results = $progressionRepository->findByCriteria($user,$title,$status);

        if (!$results ) {
            return new JsonResponse(['message' => 'Aucun cours pour ces critères'], 404);
        }
     
        $serializedCourses = $serializer->serialize($results, 'json', ['groups' => ['course', 'course_professor', 'course_category', 'category', 'course_composers' , 'course_composers' ,'course_instruments' , 'instrument']]);

        return new JsonResponse($serializedCourses, 200, [], true);
    }

    #[Route('/new-progression', name: 'new_progression', methods: ['POST'])]
    public function create(ManagerRegistry $doctrine, HttpFoundationRequest $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userOneId = $doctrine->getRepository(User::class)->find($data['userOneId']);
        $courseId = $doctrine->getRepository(Course::class)->find($data['courseId']);
        $percentageWatched =  $data['percentageWatched'];
        $videoTimer = $data['videoTimer'];

        if (!$userOneId || !$courseId) {
            $textError = "Attention, Le cours ou l'utilisateur n'existe pas";
            return new JsonResponse($textError, 404, [], true);
        }

        $progression = $entityManager->getRepository(Progression::class)->findByProgression($data['userOneId'],$data['courseId']);

        if(!$progression) {
  
            $progression = new Progression();
            $progression->setUser($userOneId);
            $progression->setCourse($courseId);
            $progression->setVideoTimestamp($videoTimer);
            $progression->setStatus($this->status[0]);
            $progression->setQuizzStatus($this->status[0]);
            $progression->setCreatedAt(new \DateTimeImmutable());
            $progression->setUpdateAt(new \DateTimeImmutable());
            $progression->setPercentageWatched($percentageWatched);
            $entityManager->persist($progression);
            $entityManager->flush();
        }

        $result = [];

        if ($progression ) {
            
            $result[] = [
                'progressionId' => $progression->getId(),
                'user' => $progression->getUser(),
                'course' => $progression->getCourse(),
                'percentageWatched' => $progression->getPercentageWatched(),
                'videoTimestamp' => $progression->getVideoTimestamp(),
                'courseStatus' => $progression->getStatus(),
                'quizzStatus' => $progression->getQuizzStatus(),
                'creatAt' =>  $progression->getCreatedAt(),
                'updateAt' =>  $progression->getUpdateAt()
            ];
        }
        $json = $this->serializer->serialize($result, 'json', ['groups' => ['course','course_professor','user','course_comments',  'progression' , 'course_composers'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }


    #[Route('/update-progression', name: 'update_progression', methods: ['PUT'])]
    public function updateProgression (ManagerRegistry $doctrine, HttpFoundationRequest $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userOneId = $doctrine->getRepository(User::class)->find($data['userOneId']);
        $courseId = $doctrine->getRepository(Course::class)->find($data['courseId']);
        $percentageWatched = (int) $data['percentageWatched'];
        $videoTimer = $data['videoTimer'];

        if (!$userOneId || !$courseId) {
            $textError = "Attention, Le cours ou l'utilisateur n'existe pas";
            return new JsonResponse($textError, 404, [], true);
        }

        $progression = $entityManager->getRepository(Progression::class)->findByProgression($data['userOneId'],$data['courseId']);

        if (!$progression) {
            $progression = new Progression();
            $progression->setUser($userOneId);
            $progression->setCourse($courseId);
        }        

        $progression->setVideoTimestamp($videoTimer);
        $progression->setStatus( $percentageWatched === 100 ? $this->status[2] : $this->status[1] );
        $progression->setQuizzStatus($percentageWatched === 100 ? $this->quizzStatus[1] : $this->quizzStatus[0]);
        $progression->setUpdateAt(new \DateTimeImmutable());
        $progression->setPercentageWatched($percentageWatched);
        $entityManager->persist($progression);
        $entityManager->flush();
        
        $datasUpdate[] = [
            'progressionId' => $progression->getId(),
            'user' => $progression->getUser(),
            'course' => $progression->getCourse(),
            'percentageWatched' => $progression->getPercentageWatched(),
            'videoTimestamp' => $progression->getVideoTimestamp(),
            'courseStatus' => $progression->getStatus(),
            'quizzStatus' => $progression->getQuizzStatus(),
            'creatAt' =>  $progression->getCreatedAt(),
            'updateAt' =>  $progression->getUpdateAt(),
        ];

        $json = $this->serializer->serialize($datasUpdate, 'json', ['groups' => ['course','course_professor', 'user','progression','course_composers'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
        
    }


    #[Route('/progression/{id}', name: 'progression_show', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, SerializerInterface $serializer, int $id): JsonResponse
    {
        $progression = $doctrine->getRepository(Progression::class)->find($id);
        if (!$progression) {
            return new JsonResponse("Aucune progression n'a été trouvé", 404, [], true);
        }

        $data =  [
                'progressionId' => $progression->getId(),
                'user' => $progression->getUser(),
                'course' => $progression->getCourse(),
                'percentageWatched' => $progression->getPercentageWatched(),
                'videoTimestamp' => $progression->getVideoTimestamp(),
                'courseStatus' => $progression->getStatus(),
                'quizzStatus' => $progression->getQuizzStatus(),
                'creatAt' =>  $this->formatter->format($progression->getCreatedAt()),
                'updateAt' =>  $this->formatter->format($progression->getUpdateAt()),
        ];

        $json = $serializer->serialize($data, 'json', ['groups' => ['course', 'course_professor', 'user','progression','course_composers'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/delete-progression/{id}', name: 'delete_progression', methods: ['DELETE'])]
    public function removeMessage(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $messageRepository = $entityManager->getRepository(Progression::class);
        $message = $messageRepository->find($id);

        if (!$message) {
            return new JsonResponse('Progression not found', 404);
        }

        $entityManager->remove($message);
        $entityManager->flush();

        return new JsonResponse('progression removed successfully');
    }

}
