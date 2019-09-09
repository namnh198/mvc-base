<?php

namespace System\Http;

class UploadFile
{
    private $app;
    
    private $files = [];

    private $error;

    private $allowedImage = ['gif', 'jpg', 'jpeg', 'png', 'webp'];

    public function __construct($input)
    {
        $this->getInfoFile($input);
    }

    private function getInfoFile($input)
    {
        if(empty($_FILES[$input])) 
        {
            return;
        }

        $file = $_FILES[$input];
        $fileNameInfo = pathinfo($file['name']);

        $this->error = $file['error'];

        if($this->error !== UPLOAD_ERR_OK)
        {
            return;
        }

        $this->files = [
            'file' => $file,
            'filename' => $file['name'],
            'nameonly' => $fileNameInfo['basename'],
            'extension' => strtolower($fileNameInfo['extension']),
            'mimefile' => $file['type'],
            'tmpfile' => $file['tmp_name'],
            'size' => $file['size'],
        ];
    }

    public function exists()
    {
        return $this->files['file'] ?? null;
    }

    public function getFileName()
    {
        return $this->files['filename'] ?? null;
    }

    public function getNameOnly()
    {
        return $this->files['nameonly'] ?? null;
    }

    public function getExtension()
    {
        return $this->files['extension'] ?? null;
    }

    public function getMimeType()
    {
        return $this->files['mimefile'] ?? null;
    }

    public function isImage()
    {
        return strpos($this->getMimeType(), 'image/') === 0 
                && in_array($this->getExtension(), $this->allowedImage);
    }

    public function moveTo($target, $newFileName = null)
    {    
        $target = file_to('public/' . $target);

        $fileName = ($newFileName) ? $newFileName.'.'.$this->getExtension() : $this->getFileName();

        if (! is_dir($target)) {
            mkdir($target, 0777, true);
        }

        $uploadedFilePath = rtrim($target , '/') . '/' . $fileName;

        if(! file_exists($uploadedFilePath))
        {
            move_uploaded_file($this->files['tmpfile'], $uploadedFilePath);
        }
        
        return $fileName;
    }
}