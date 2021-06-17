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

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'required' => TRUE,
                'label' => 'security.register.user.firstname'
            ])
            ->add('lastname', TextType::class, [
                'required' => TRUE,
                'label' => 'security.register.user.lastname'
            ])
            ->add('username', TextType::class, [
                'required' => TRUE,
                'label' => 'security.register.user.username'
            ])
            ->add('plainPassword', TextType::class, [
                'required' => TRUE,
                'label' => 'security.register.user.plainPassword'
            ])
            ->add('email', EmailType::class, [
                'required' => TRUE,
                'label' => 'security.register.user.email'
            ])
            ->add('company', RegisterCompanyType::class, [
                'required' => TRUE,
                'label' => ' '
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
