<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class UtilitiesService
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    /**
     * Génère un nom de fichier unique et sécurisé.
     */
    public function generateUniqueLongFileName(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        
        // Ex: produit-chocolat-3a1b2c4d5e6f.jpg
        return $safeFilename . '-' . md5(uniqid('', true)) . '.' . $file->guessExtension();
    }
}