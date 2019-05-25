<?php

namespace App\Controller;

use App\Entity\Address;
use App\Repository\AddressRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AddressBookController extends Controller
{
    public function __construct(AddressRepository $addresse_repo)
    {
        $this->addresse_repo = $addresse_repo;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $paginator = $this->get('knp_paginator');

        $all_addresses = $this->addresse_repo->findAll();

        $addresses = $paginator->paginate(
            $all_addresses,
            $page
        );

        return $this->render('address_book/index.html.twig', [
            'addresses' => $addresses,
        ]);
    }

    /**
     * @Route("/create", name="create_address")
     */
    public function create()
    {
        return $this->render('address_book/create.html.twig');
    }

    /**
     * @Route("/save", name="save_address" )
     */
    public function save(Request $request)
    {
        $address = new Address();

        $address->setZip($request->get('zip'))
                ->setCity($request->get('city'))
                ->setEmail($request->get('email'))
                ->setCountry($request->get('country'))
                ->setBirthDay($request->get('birthday'))
                ->setLastname($request->get('lastname'))
                ->setFirstname($request->get('firstname'))
                ->setPhonenumber($request->get('phonenumber'))
                ->setStreetnumber($request->get('streetnumber'));

        // Dump($address);

        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }
}
