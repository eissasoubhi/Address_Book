<?php

namespace App\Service;

use App\Entity\Address;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AddressService
{
    private $container;
    private $fileSystem;

    public function __construct(ContainerInterface $container, Filesystem $fileSystem)
    {
        $this->container = $container;
        $this->fileSystem = $fileSystem;
    }

    public function uploadPicture(UploadedFile $picture_file): string
    {
        $file_name = md5(uniqid()).'.'.$picture_file->guessExtension();

        try {
            $picture_file->move(
                $this->container->getParameter('pictures_directory'),
                $file_name
            );
        } catch (FileException $e) {
            throw new Exception("An error occurred while upload the image!");
        }

        return $file_name;
    }

    public function handlePictureinDb(Request $request, Address $updated_address, Address $old_address)
    {
        if ( ! $request->get('delete_picture', false)) {
            // keep the picture saved in the database
            $updated_address->setPicture($old_address->getPicture());
        } else {
            // delete the picture from the data base
            $updated_address->setPicture(null);
            // delete the picture from the pictures folder
            $this->deletePictureFromFolder($old_address);
        }
    }

    /**
     * if the picture is deleted from the database, we delete it from the pictures folder too
     */
    public function deletePictureFromFolder(Address $address)
    {
        $old_picture = $this->container->getParameter('pictures_directory') . $address->getPicture();
        if ($address->getPicture() && $this->fileSystem->exists($old_picture)) {
            $this->fileSystem->remove(array($old_picture));
        }
    }
}