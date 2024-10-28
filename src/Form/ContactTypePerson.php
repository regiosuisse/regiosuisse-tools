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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactTypePerson extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Personal Information
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Vorname',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getFirstName(),
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nachname',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getLastName(),
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Geschlecht',
                'choices' => [
                    'Männlich' => 'male',
                    'Weiblich' => 'female',
                    'Divers' => 'diverse',
                ],
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getGender(),
            ])
            ->add('academicTitle', TextType::class, [
                'label' => 'Akademischer Titel',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getAcademicTitle(),
            ])
            // Contact Information
            ->add('email', EmailType::class, [
                'label' => 'E-Mail',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getEmail(),
            ])
            ->add('phone', TelType::class, [
                'label' => 'Telefon',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getPhone(),
            ])
            ->add('website', UrlType::class, [
                'label' => 'Website',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getWebsite(),
            ])
            // Address Information
            ->add('street', TextType::class, [
                'label' => 'Straße',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getStreet(),
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'PLZ',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getZipCode(),
            ])
            ->add('city', TextType::class, [
                'label' => 'Stadt',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getCity(),
            ])
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'name',
                'label' => 'Land',
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Wählen Sie ein Land',
                'data' => $options['data']->getCountry(),
            ])
            // Additional Information
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'label' => 'Sprache',
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Wählen Sie eine Sprache',
                'data' => $options['data']->getLanguage(),
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getDescription(),
            ]);

        // Bind translations for DE
        $builder->add('translations_fr_website', UrlType::class, [
            'label' => 'Website (FR)',
            'mapped' => false,
            'required' => false,
            'data' => $options['data']->getTranslations()['fr']['website'] ?? '',
        ]);
        $builder->add('translations_fr_city', TextType::class, [
            'label' => 'Stadt (FR)',
            'mapped' => false,
            'required' => false,
            'data' => $options['data']->getTranslations()['fr']['city'] ?? '',
        ]);
        $builder->add('translations_fr_description', TextareaType::class, [
            'label' => 'Beschreibung (FR)',
            'mapped' => false,
            'required' => false,
            'data' => $options['data']->getTranslations()['fr']['description'] ?? '',
        ]);

        // Bind translations for IT
        $builder->add('translations_it_website', UrlType::class, [
            'label' => 'Website (IT)',
            'mapped' => false,
            'required' => false,
            'data' => $options['data']->getTranslations()['it']['website'] ?? '',
        ]);
        $builder->add('translations_it_city', TextType::class, [
            'label' => 'Stadt (IT)',
            'mapped' => false,
            'required' => false,
            'data' => $options['data']->getTranslations()['it']['city'] ?? '',
        ]);
        $builder->add('translations_it_description', TextareaType::class, [
            'label' => 'Beschreibung (IT)',
            'mapped' => false,
            'required' => false,
            'data' => $options['data']->getTranslations()['it']['description'] ?? '',
        ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'employments' => [], // Set default empty array for employments

        ]);
    }
}

