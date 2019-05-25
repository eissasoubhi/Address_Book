<?php

namespace App\Controller;

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
     * @Route("/", name="address_book")
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
}
