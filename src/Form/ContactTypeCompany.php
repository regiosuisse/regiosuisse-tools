<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\Country;
use App\Entity\State;
use App\Entity\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
// Form types
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ContactTypeCompany extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Personal Information
            ->add('firstName', TextType::class, [
                'label' => 'Vorname',
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nachname',
                'required' => false,
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Geschlecht',
                'choices' => [
                    'Männlich' => 'male',
                    'Weiblich' => 'female',
                    'Divers' => 'diverse',
                ],
                'required' => false,
            ])
            ->add('academicTitle', TextType::class, [
                'label' => 'Akademischer Titel',
                'required' => false,
            ])
            // Contact Information
            ->add('email', EmailType::class, [
                'label' => 'E-Mail',
                'required' => false,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Telefon',
                'required' => false,
            ])
            ->add('website', UrlType::class, [
                'label' => 'Website',
                'required' => false,
            ])
            // Address Information
            ->add('street', TextType::class, [
                'label' => 'Straße',
                'required' => false,
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'PLZ',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'Stadt',
                'required' => false,
            ])
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'name',
                'label' => 'Land',
                'required' => false,
                'placeholder' => 'Wählen Sie ein Land',
            ])
            // Company Information
            ->add('companyName', TextType::class, [
                'label' => 'Firmenname',
                'required' => false,
            ])
            ->add('specification', TextType::class, [
                'label' => 'Zusatzangaben Firma/Institution',
                'required' => false,
            ])
            ->add('parent', TextType::class, [
                'label' => 'Übergeordnete Organisation',
                'required' => false,
            ])
            // Additional Information
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'label' => 'Sprache',
                'required' => false,
                'placeholder' => 'Wählen Sie eine Sprache',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'required' => false,
            ]);
    }
}
