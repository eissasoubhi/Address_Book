<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Service\AddressService;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface ;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form as FormBuilder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AddressBookController extends Controller
{
    public function __construct(
        Filesystem $fileSystem,
        EntityManagerInterface $em,
        AddressRepository $address_repo,
        AddressService $address_service
    )
    {
        $this->em = $em;
        $this->fileSystem = $fileSystem;
        $this->address_repo = $address_repo;
        $this->address_service = $address_service;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $paginator = $this->get('knp_paginator');
        $address = new Address();
        // address delete button
        $delete_address_form = $this->createFormBuilder($address)
            ->add('id', HiddenType::class)
            ->getForm();
        // show last added addresses at the top
        $all_addresss = $this->address_repo->findAllOrderBy('DESC');

        $addresses = $paginator->paginate(
            $all_addresss,
            $page
        );

        return $this->render('address_book/index.html.twig', [
            'addresses' => $addresses,
            'delete_address_form' => $delete_address_form->createView()
        ]);
    }

    /**
     * @Route("/create", name="create_address")
     */
    public function create(Request $request)
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->getData();
            // if a picture was uploaded
            if ($picture_file = $form->get('picture')->getData()) {
                try {
                    $file_name = $this->address_service->uploadPicture($picture_file);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        $e->getMessage()
                    );
                    return $this->redirectToRoute('home');
                }

                $address->setPicture($file_name);
            }

            $this->em->persist($address);
            $this->em->flush();

            $this->addFlash(
                'success',
                'New address saved successfully!'
            );

            return $this->redirectToRoute('home');
        }

        return $this->render('address_book/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit_address")
     */
    public function edit(Address $address, Request $request)
    {
        $form = $this->createForm(AddressType::class, $address);
        // save the old address, the order is important
        $old_address = clone $address;
        $form->handleRequest($request);
        $updated_address = $address;

        if ($form->isSubmitted() && $form->isValid()) {
            $updated_address = $form->getData();
            $this->address_service->handlePictureInDb($request, $updated_address, $old_address);
            // if the picture was uploaded
            if ($picture_file = $form->get('picture')->getData()) {
                try {
                    $file_name = $this->address_service->uploadPicture($picture_file);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        $e->getMessage()
                    );
                    return $this->redirectToRoute('home');
                }

                 // if the picture is deleted from the database, we delete it from the pictures folder too
                $this->address_service->deletePictureFromFolder($old_address);
                $updated_address->setPicture($file_name);
            }

            $this->em->persist($updated_address);
            $this->em->flush();

            $this->addFlash(
                'success',
                'The address is updated successfully!'
            );

            return $this->redirectToRoute('home');
        }

        return $this->render('address_book/edit.html.twig', [
            'form' => $form->createView(),
            'address' => $updated_address
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete_address", methods={"POST"})
     */
    public function delete(Address $address)
    {
        $picture = $this->getParameter('pictures_directory') . $address->getPicture();

        if (empty($address)) {
            $this->addFlash('error', 'The address is not found!');
            return $this->redirectToRoute('home');
        }

        $this->em->remove($address);
        $this->em->flush();

        if( ! empty($address->getPicture()) && $this->fileSystem->exists($picture)) {
            $this->fileSystem->remove(array($picture));
        }

        $this->addFlash('success', 'The address is removed!');

        return $this->redirectToRoute('home');
    }
}
