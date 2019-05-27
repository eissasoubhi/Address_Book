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

    /**
     * @var Symfony\Component\Filesystem\Filesystem
     */
    protected $fileSystem;

    /**
     * @var Doctrine\ORM\EntityManagerInterface entity manager
     */
    protected $em;

    /**
     * @var App\Repository\AddressRepository
     */
    protected $address_repo;

    /**
     * @var App\Service\AddressService
     */
    protected $address_service;

    /**
     *  @param Symfony\Component\Filesystem\Filesystem $fileSystem
     *  @param Doctrine\ORM\EntityManagerInterface $em
     *  @param App\Repository\AddressRepository $address_repo
     *  @param App\Service\AddressService $address_service
     */
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
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $paginator = $this->get('knp_paginator');

        // get last added addresses at the top
        $all_addresss = $this->address_repo->findAllOrderBy('DESC');

        $addresses = $paginator->paginate(
            $all_addresss,
            $page
        );

        return $this->render('address_book/index.html.twig', [
            'addresses' => $addresses
        ]);
    }

    /**
     * @Route("/create", name="create_address")
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     */
    public function create(Request $request)
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->getData();
            $picture = $form->get('picture')->getData();

            try {
                // if the picture was uploaded
                if ($picture) {
                    $address = $this->address_service->savePictureToAddress($picture, $address);
                }
            } catch (FileException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirectToRoute('home');
            }

            $this->em->persist($address);
            $this->em->flush();

            $this->addFlash('success', 'New address saved successfully!');

            return $this->redirectToRoute('home');
        }

        return $this->render('address_book/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit_address")
     *
     * @param App\Entity\Address $address
     * @param Symfony\Component\HttpFoundation\Request $request
     */
    public function edit(Address $address, Request $request)
    {
        $form = $this->createForm(AddressType::class, $address);
        // save the old address data, the order is important
        $old_address = clone $address;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $edited_address = $form->getData();
            // whether we keep or delete the address picture
            $edited_address = $this->address_service->handleAddressPicture($request, $edited_address, $old_address);
            $picture = $form->get('picture')->getData();

            try {
                if ($picture) {
                    // we update the address picture if it was uploaded
                    $this->address_service->updateAddressPicture($edited_address, $picture);
                    // then we delete the old picture from the pictures folder
                    $this->address_service->deleteAdressPicture($old_address);
                }
            } catch (FileException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirectToRoute('home');
            }

            $this->em->persist($edited_address);
            $this->em->flush();

            $this->addFlash('success', 'The address is updated successfully!');

            return $this->redirectToRoute('home');
        }

        return $this->render('address_book/edit.html.twig', [
            'form' => $form->createView(),
            'address' => $edited_address ?? $address
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete_address", methods={"POST"})
     *
     * @param App\Entity\Address $address
     * @param Symfony\Component\HttpFoundation\Request $request
     */
    public function delete(Address $address, Request $request)
    {
        if (empty($address)) {
            $this->addFlash('error', 'The address is not found!');

            return $this->redirectToRoute('home');
        }

        $submittedToken = $request->request->get('token');

        // 'delete-address' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('delete-address', $submittedToken)) {

            $this->em->remove($address);
            $this->em->flush();
            $this->address_service->deleteAdressPicture($address);

            $this->addFlash('success', 'The address is deleted!');

            return $this->redirectToRoute('home');
        } else {

            $this->addFlash('error', 'Invalid token!');

            return $this->redirectToRoute('home');
        }
    }
}
