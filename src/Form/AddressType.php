<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class AddressType extends AbstractType
{
    /**
     * @param Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('picture', FileType::class, ['required' => false, 'data_class' => null ])
        ->add('firstname', TextType::class, ['label' => 'First name'])
        ->add('lastname', TextType::class, ['label' => 'Last name'])
        ->add('streetnumber', TextType::class, ['label' => 'Street and number'])
        ->add('zip', IntegerType::class)
        ->add('city', TextType::class)
        ->add('phonenumber', TextType::class, ['label' => 'Phone number'])
        ->add('birthDay', TextType::class)
        ->add('email', TextType::class)
        ->add('country', TextType::class)
        ->add('save', SubmitType::class, ['label' => 'Create Address'])
        ->getForm();
    }

    /**
     * @param Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
