<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'required' => TRUE,
                'label' => 'admin.users.list.firstname'
            ])
            ->add('lastname', TextType::class, [
                'required' => TRUE,
                'label' => 'admin.users.list.lastname'
            ])
            ->add('email', EmailType::class, [
                'required' => TRUE,
                'label' => 'admin.users.list.email'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'form.save',
                'attr' => [
                    'class' => 'button button--primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}