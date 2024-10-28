<?php

namespace App\Form;

use App\Entity\Employment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmploymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $employment = $builder->getData(); // Get the employment object
        error_log('EmploymentType: ' . print_r($employment, true));
        $builder
            ->add('role', TextType::class, [
                'label' => 'Rolle',
                'required' => false,
            ])
            ->add('company', TextType::class, [
                'label' => 'Firma',
                'mapped' => false,
                'required' => false,
            ])
            ->add('iDontWorkHereAnymore', CheckboxType::class, [
                'label' => 'Ich arbeite hier nicht mehr',
                'required' => false,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Employment::class,
        ]);
    }
}

