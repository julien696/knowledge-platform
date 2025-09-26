<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    public function serve(string $path): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $path;
        
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('File not found');
        }
        
        return new BinaryFileResponse($filePath);
    }
}
