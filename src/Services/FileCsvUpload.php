<?php

namespace App\Services;

use Symfony\Component\Validator\Constraints as Assert;

class FileCsvUpload
{


    /**
     * @Assert\NotBlank(message="Veuillez joindre un fichier au format .csv")
     */
    private $file;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

}
