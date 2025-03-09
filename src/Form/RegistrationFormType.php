<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends BaseUserFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajouter les champs communs
        $this->addCommonFields($builder, $options);
        
        // Ajouter le champ spécifique à ce formulaire
        $builder->add('adminCode', TextType::class, [
            'label' => false,
            'mapped' => false,
            'required' => false,
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Code administrateur (optionnel)'
            ]
        ]);
    }
}
