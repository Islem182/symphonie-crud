<?php

namespace App\Form;

use App\Entity\PropertySearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PropertySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Article Name',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter article name'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Define the data class related to this form
            'data_class' => PropertySearch::class,
            'method' => 'GET', // Using GET for search
            'csrf_protection' => false, // Disable CSRF for search forms
        ]);
    }
}
