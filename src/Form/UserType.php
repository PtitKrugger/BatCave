<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
#use Symfony\Component\Form\Extension\Core\Type\EmailType;
#use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            #->add('email', EmailType::class)
            ->add('username', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('profilePicture', VichImageType::class, [
                'required' => false,
                'allow_delete' => false, // Permet de supprimer l'image
                'download_uri' => false, // Permet de télécharger l'image
                'image_uri' => false, // Affiche l'image actuelle
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (jpeg, png, gif).',
                    ])
                ],
            ])
            ->add('profileBackground', VichImageType::class, [
                'required' => false,
                'allow_delete' => false, // Permet de supprimer l'image
                'download_uri' => false, // Permet de télécharger l'image
                'image_uri' => false, // Affiche l'image actuelle
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (jpeg, png, gif).',
                    ])
                ],
            ])
            ->add('profileBorderColor', ColorType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
