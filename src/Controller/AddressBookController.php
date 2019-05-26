<?php

namespace App\Controller;

use App\Entity\Address;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form as FormBuilder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class AddressBookController extends Controller
{
    public function __construct(AddressRepository $addresse_repo, EntityManagerInterface $em)
    {
        $this->addresse_repo = $addresse_repo;
        $this->em = $em;
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
    public function create(Request $request)
    {
        $address = new Address();

        $form = $this->createAddressForm($address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $addresse = $form->getData();

            $this->em->persist($address);
            $this->em->flush();

            $this->addFlash(
                'success',
                'Saved successfully!'
            );

            return $this->redirectToRoute('home');
        }

        return $this->render('address_book/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    protected function createAddressForm(Address $address): FormBuilder
    {
        return  $this->createFormBuilder($address)
        ->add('picture', FileType::class, ['required' => false ])
        ->add('firstname', TextType::class)
        ->add('lastname', TextType::class)
        ->add('streetnumber', TextType::class, ['label' => 'Street and number'])
        ->add('zip', IntegerType::class)
        ->add('city', TextType::class)
        ->add('phonenumber', TextType::class)
        ->add('birthDay', TextType::class)
        ->add('email', TextType::class)
        ->add('country', TextType::class)
        ->add('save', SubmitType::class, ['label' => 'Create Address'])
        ->getForm();
    }
}
