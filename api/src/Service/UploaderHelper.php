<?php

namespace App\Service;

use App\Entity\Course;
use Psr\Log\LoggerInterface;
use League\Flysystem\Filesystem;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Asset\Context\RequestStackContext;

class UploaderHelper
{   
    const SCORE_FOLDER = 'scores';

    private $publicUploadFilesystem;
    private $requestStackContext;
    private $logger;

    public function __construct(RequestStackContext $requestStackContext, Filesystem $publicUploadFilesystem, LoggerInterface $logger)
    {
        $this->publicUploadFilesystem = $publicUploadFilesystem;
        $this->requestStackContext = $requestStackContext;
        $this->logger = $logger;
    }

    public function uploadCourseReference(File $file): string
    {
        return $this->uploadFile($file, self::SCORE_FOLDER, true);
    }

    public function getPublicPath(string $path): string
    {
        return $this->requestStackContext
            ->getBasePath().'/uploads'.$path;
    }

    public function readStream(string $path, bool $isPublic)
    {
        $filesystem = $this->publicUploadFilesystem;

        $resource = $filesystem->readStream($path);

        if($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }

        return $resource;
    }

    public function deleteFile(string $path, bool $isPublic)
    {
        $filesystem = $this->publicUploadFilesystem;

        $result = $filesystem->delete($path);

        if($result === false) {
            throw new \Exception(sprintf('Error deleting "%s"', $path));
        }

        return $result;
    }

    public function uploadFile(File $file, bool $isPublic): string
    {
        if($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $newFilename = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME));

        $filesystem = $this->publicUploadFilesystem;
  
        $stream = fopen($file->getPathname(), 'r');

        $result = $filesystem->writeStream(
            self::SCORE_FOLDER.'/'.$newFilename,
            $stream
        );

        if($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        if(is_resource($stream)) {
            fclose($stream);
        }

        return $newFilename;

    }
}