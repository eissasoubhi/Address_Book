<?php

namespace App\Service;

use App\Entity\Address;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AddressService
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var Symfony\Component\Filesystem\Filesystem
     */
    private $fileSystem;

    /**
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param Symfony\Component\Filesystem\Filesystem @fileSystem
     */
    public function __construct(ContainerInterface $container, Filesystem $fileSystem)
    {
        $this->container = $container;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $picture
     * @param App\Entity\Address $address
     * @return App\Entity\Address
     */
    public function savePictureToAddress(UploadedFile $picture, Address $address): Address
    {
        try {
            $file_name = $this->uploadPicture($picture);
        } catch (FileException $e) {
            throw new Exception($e->getMessage());
        }
        // save the filename to the address
        $address->setPicture($file_name);

        return $address;
    }

    /**
     * Upload a picture and save its name to the retuened address
     *
     * @param App\Entity\Address $address
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $picture
     * @return App\Entity\Address
     */
    public function updateAddressPicture(Address $address, UploadedFile $picture): Address
    {
        try {
            $file_name = $this->uploadPicture($picture);
        } catch (FileException $e) {
            $this->addFlash('error', $e->getMessage()
            );
            return $this->redirectToRoute('home');
        }

        $address->setPicture($file_name);

        return $address;
    }

    /**
     * Upload a picture to the public directory and return its name
     *
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $picture
     * @return string
     */
    public function uploadPicture(UploadedFile $picture): string
    {
        $file_name = md5(uniqid()).'.'.$picture->guessExtension();

        try {
            $picture->move(
                $this->container->getParameter('pictures_directory'),
                $file_name
            );
        } catch (FileException $e) {
            throw new Exception("An error occurred while upload the image!");
        }

        return $file_name;
    }

    /**
     * Wether we copy the picture of an address to another given address,
     * or we delete that picture, based on a request parameter
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param App\Entity\Address $edited_address
     * @param App\Entity\Address $old_address
     * @return App\Entity\Address
     */
    public function handleAddressPicture(Request $request, Address $edited_address, Address $old_address): Address
    {
        if ( ! $request->get('delete_picture', false)) {
            // keep the picture saved in the database
            $edited_address->setPicture($old_address->getPicture());
        } else {
            // delete the picture from the database
            $edited_address->setPicture(null);
            // delete the picture from the pictures folder
            $this->deleteAdressPicture($old_address);
        }

        return $edited_address;
    }

    /**
     * @param App\Entity\Address $address
     */
    /**
     * if the picture is deleted from the database, we delete it from the pictures folder too
     */
    public function deleteAdressPicture(Address $address)
    {
        $picture = $this->container->getParameter('pictures_directory') . $address->getPicture();

        if ($address->getPicture() && $this->fileSystem->exists($picture)) {
            $this->fileSystem->remove(array($picture));
        }
    }
}