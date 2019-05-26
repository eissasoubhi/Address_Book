<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form as FormBuilder;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AddressRepository as AddressRepo;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface as EMInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AddressBookController extends Controller
{
    public function __construct(AddressRepo $address_repo, EMInterface $em, Filesystem $fileSystem)
    {
        $this->em = $em;
        $this->fileSystem = $fileSystem;
        $this->address_repo = $address_repo;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $paginator = $this->get('knp_paginator');
        $address = new Address();

        $delete_address_form = $this->createFormBuilder($address)
            ->add('id', HiddenType::class)
            ->getForm();

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
        $address->setBirthday('01/01/2010');
        $address->setFirstname('gkjhbj');
        $address->setLastname('gkjhbj');
        $address->setPhoneNumber('76890678');
        $address->setStreetnumber('fghkjhghgv');
        $address->setCity('Casa');
        $address->setCountry('Maroc');
        $address->setZip(76890);
        $address->setEmail(time().'.test@mail.com');
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->getData();

            if ($picture_file = $form->get('picture')->getData()) {
                $file_name = md5(uniqid()).'.'.$picture_file->guessExtension();

                try {
                    $picture_file->move(
                        $this->getParameter('pictures_directory'),
                        $file_name
                    );
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        'An error occurred while upload the image!'
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

            return $this->redirectToRoute('create_address');
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

        $old_address = clone $address;

        $form->handleRequest($request);

        $updated_address = $address;

        if ($form->isSubmitted() && $form->isValid()) {
            $updated_address = $form->getData();
            // dump($old_address->getPicture());die();
            if ( ! $request->get('delete_picture', false)) {
                $updated_address->setPicture($old_address->getPicture());
            } else {
                $updated_address->setPicture(null);

                $old_picture = $this->getParameter('pictures_directory') . $old_address->getPicture();
                if ($old_address->getPicture() && $this->fileSystem->exists($old_picture)) {
                    $this->fileSystem->remove(array($old_picture));
                }
            }

            if ($picture_file = $form->get('picture')->getData()) {

                $file_name = md5(uniqid()).'.'.$picture_file->guessExtension();

                try {
                    $picture_file->move(
                        $this->getParameter('pictures_directory'),
                        $file_name
                    );
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        'An error occurred while upload the image!'
                    );

                    return $this->redirectToRoute('home');
                }

                $old_picture = $this->getParameter('pictures_directory') . $old_address->getPicture();
                if ($old_address->getPicture() && $this->fileSystem->exists($old_picture)) {
                    $this->fileSystem->remove(array($old_picture));
                }

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
