<?php


namespace App\Controller;

use App\Entity\Course;
use App\Entity\MusicScore;
use App\Entity\CourseReference;
use Doctrine\ORM\EntityManager;
use App\Service\UploaderHelper;
use Gedmo\Sluggable\Util\Urlizer;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CourseReferenceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ScoreController extends AbstractController
{
    // // @ is granted here for only admins
    #[Route('/course/{id}/fileupload', name: 'new_upload', methods: ['POST'])]
    public function uploadMusicScore(Request $request, Course $course, $id, SerializerInterface $serializer, ValidatorInterface $validator, UploaderHelper $uploaderHelper, EntityManagerInterface $em)
    {
        if($request->headers->get('Content-Type') === 'application/json') {
            // turn json into object deserializer
            $musicFile = $serializer->deserialize(
                $request->getContent(),
                MusicScore::class,
                'json'
            );

            $violations = $validator->validate($musicFile);
            if($violations->count() > 0) {
                return $this->json($violations, 400);
            }

            $tmpPath = sys_get_temp_dir().'/sf_upload'.uniqid();
            file_put_contents($tmpPath, $musicFile->getDecodedData());
            $uploadedFile = new FileObject($tmpPath);
            $originalName = $musicFile->filename;
        }

        $violations = $validator->validate(
            $uploadedFile,
            new File([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/*',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'text/plain'
                ]
            ])
        );

            // dd($upload);
            $filename = $uploaderHelper->uploadCourseReference($uploadedFile);
            // ! temporary debugging
            // $filename = 'score.pdf';

            // create course reference
            $courseReference = new CourseReference($course);
            // store unique filename
            $courseReference->setFilename($filename);
            // getClientOrignalName, to the filename given in MusicScore
            $courseReference->setOriginalFilename($originalName ?? $filename);
            // application/octect-stream as fallback if getMimeType returns null
            $courseReference->setMimeType($uploadedFile->getMimeType() ?? 'application/octect-stream');
            // delete temporary file
            if(is_file($uploadedFile->getPathname())) {
                unlink($uploadedFile->getPathname());
            }

            $em->persist($courseReference);

            $em->flush();

            // dd($courseReference);
            // return new JsonResponse(['message' => 'Score uploaded'], 201);
            return $this->json(
                $courseReference,
                201,
                [],
                [
                    'groups' => ['main']
                ]
            );
    }

    #[Route('/course/all/fileupload', name: 'all_uploads', methods: ['GET'])]
    public function getAllScores(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, CourseReferenceRepository $courseRefRepository)
    {
        $courseReferences = $courseRefRepository->findAllWithCourse();
    
        // Modify the serialized data to include Course ID
        $serializedCourseReferences = [];
        foreach ($courseReferences as $courseReference) {
            $serializedData = $serializer->normalize($courseReference, null, ['groups' => ['main']]);
            
            // Retrieve the Course ID from the Course entity
            $courseId = $courseReference->getCourse() ? $courseReference->getCourse()->getId() : null;
    
            // Add Course ID to the serialized data
            $serializedData['course_id'] = $courseId;
    
            $serializedCourseReferences[] = $serializedData;
        }
    
        // Convert the array data to a JSON string
        $jsonData = json_encode($serializedCourseReferences);
    
        // Create the JsonResponse with the JSON data
        return new JsonResponse($jsonData, 200, [], true);
    }
    
    

    #[Route('/course/{id}/fileuploads', name: 'bycourse_uploads', methods: ['GET'])]
    public function getScoresByCourse(Request $request, Course $course, $id, SerializerInterface $serializer, CourseReferenceRepository $courseRefRepository): JsonResponse
    {

        $courseReferences = $courseRefRepository->findByCourseId($id);

        // Serialize the CourseReference objects to JSON
        $jsonData = $serializer->serialize($courseReferences, 'json', [
            'groups' => ['main'], // Replace 'main' with your serialization group if needed
        ]);

        return new JsonResponse($jsonData, 200, [], true);
       
    }


    // // add here granted only for admins
    #[Route('/course/{id}/fileupload/{idScore}', name: 'delete_upload', methods: ['DELETE'])]
    public function deleteMusicScore(Request $request, Course $course, $id, SerializerInterface $serializer, ValidatorInterface $validator, UploaderHelper $uploaderHelper, EntityManagerInterface $em, int $idScore, CourseReferenceRepository $courseRefRepository): JsonResponse
    {
        // dd($idScore, $id);
        $courseReference = $courseRefRepository->find($idScore);
        // dd($courseReference);
        // delete CourseReference
        if (!$courseReference) {
            return new JsonResponse("File not found", 404);
        }

        $em->remove($courseReference);
        $em->flush();


        // dd($courseReference);
        $uploaderHelper->deleteFile($courseReference->getFilePath(), true);
        
        // return new JsonResponse(['message' => 'File deleted'], Response::HTTP_OK);
        return new JsonResponse("File deleted succesfully", 201);
       
    }

       // add here granted only for admins
       #[Route('/course/{idScore}/fileupload/{id}', name: 'get_upload', methods: ['GET'])]
       public function donwloadScore(Request $request, CourseReference $reference, $id, SerializerInterface $serializer, ValidatorInterface $validator, UploaderHelper $uploaderHelper, EntityManagerInterface $em, int $idScore, CourseReferenceRepository $courseRefRepository)
       {

           $response = new StreamedResponse(function() use($reference, $uploaderHelper) {
                $outputStream = fopen('php://output', 'wb');
                $fileStream = $uploaderHelper->readStream($reference->getFilePath(), false);

                stream_copy_to_stream($fileStream, $outputStream);
           });
        //    
           $response->headers->set('Content-Type', $reference->getMimeType());

        //    using symfony disposition command
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $reference->getOriginalFilename()
        );

        $response->headers->set('Content-Disposition', $disposition);
        
        return $response;

       }
}