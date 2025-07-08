<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\Country;
use App\Entity\Language;
use App\Entity\Topic;
use App\Entity\State;
use App\Util\PvTrans;
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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactTypePerson extends AbstractType
{    
    private TranslatorInterface $translator;
    private string $locale;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Helper method to translate messages using the stored locale.
     */
    protected function trans(string $id, array $parameters = [], string $domain = 'messages+intl-icu'): string
    {
        return $this->translator->trans($id, $parameters, $domain, $this->locale);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->locale = $options['locale'];

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
                'placeholder' => $this->trans('form.gender_placeholder'),
                'choices' => [
                    $this->trans('form.gender_male') => 'male',
                    $this->trans('form.gender_female') => 'female',
                    $this->trans('form.gender_diverse') => 'diverse',
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
            ->add('linkedIn', UrlType::class, [
                'label' => 'LinkedIn',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getLinkedIn(),
            ])
            ->add('website', UrlType::class, [
                'label' => 'Website',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getWebsite(),
            ])
            // Address Information
            ->add('street', TextType::class, [
                'label' => 'Strasse',
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
                'choice_label' => function (Country $country) {
                    return PvTrans::trans($country, 'name', $this->locale);
                },
                'label' => 'Land',
                'mapped' => false,
                'required' => false,
                'placeholder' => $this->trans('form.country_placeholder'),
                'data' => $options['data']->getCountry(),
            ])
            ->add('state', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'name',
                'label' => 'Kanton',
                'mapped' => false,
                'required' => false,
                'placeholder' => $this->trans('form.state_placeholder'),
                'data' => $options['data']->getState(),
            ])
            // Additional Information
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => function (Language $language) {
                    return PvTrans::trans($language, 'name', $this->locale);
                },
                'label' => 'Sprache',
                'mapped' => false,
                'required' => false,
                'placeholder' => $this->trans('form.language_placeholder'),
                'data' => $options['data']->getLanguage(),
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getDescription(),
            ])
            // French Translations
            ->add('translations_fr_website', UrlType::class, [
                'label' => 'Website (FR)',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getTranslations()['fr']['website'] ?? '',
            ])
            ->add('translations_fr_city', TextType::class, [
                'label' => 'Stadt (FR)',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getTranslations()['fr']['city'] ?? '',
            ])
            ->add('translations_fr_description', TextareaType::class, [
                'label' => 'Beschreibung (FR)',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getTranslations()['fr']['description'] ?? '',
            ])
            // Italian Translations
            ->add('translations_it_website', UrlType::class, [
                'label' => 'Website (IT)',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getTranslations()['it']['website'] ?? '',
            ])
            ->add('translations_it_city', TextType::class, [
                'label' => 'Stadt (IT)',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getTranslations()['it']['city'] ?? '',
            ])
            ->add('translations_it_description', TextareaType::class, [
                'label' => 'Beschreibung (IT)',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getTranslations()['it']['description'] ?? '',
            ])
            ->add('topics', EntityType::class, [
                'label' => 'Themen',
                'class' => Topic::class,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'choices' => $options['topics'],
                'data' => new ArrayCollection($options['data']->getTopics()->toArray()),
                'choice_label' => function (Topic $topic) {
                    return PvTrans::trans($topic, 'name', $this->locale);
                },
                'mapped' => false,
            ])
            ->add('userComment', TextareaType::class, [
                'label' => 'Kommentar',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']->getUserComment(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'employments' => [],
            'topics' => [],
            'locale' => 'de',
        ]);
    }
}
