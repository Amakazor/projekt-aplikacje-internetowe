<?php


namespace App\Form;


use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterCompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => TRUE,
                'label' => 'security.register.company.name'
            ])
            ->add('address', TextType::class, [
                'required' => TRUE,
                'label' => 'security.register.company.address'
            ])
            ->add('identifier', TextType::class, [
                'required' => TRUE,
                'label' => 'security.register.company.identifier'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'form.register'
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}