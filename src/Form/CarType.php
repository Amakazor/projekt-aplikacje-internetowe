<?php

namespace App\Form;

use App\Entity\Car;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CarType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('brand', TextType::class, [
                'required' => TRUE,
                'label' => 'admin.cars.list.brand'
            ])
            ->add('model', TextType::class, [
                'required' => TRUE,
                'label' => 'admin.cars.list.model'
            ])
            ->add('horsepower', NumberType::class, [
                'required' => TRUE,
                'label' => 'admin.cars.list.horsepower'
            ])
            ->add('engine', TextType::class, [
                'required' => TRUE,
                'label' => 'admin.cars.list.engine'
            ])
            ->add('year', ChoiceType::class, [
                'required' => TRUE,
                'label' => 'admin.cars.list.year',
                'choices' => $this->buildYearChoices(),
                'attr' => ['class' => 'stylizedSelect']
            ])
            ->add('color', TextType::class, [
                'required' => TRUE,
                'label' => 'admin.cars.list.color'
            ])
            ->add('description', TextareaType::class, [
                'required' => TRUE,
                'label' => 'admin.cars.list.description'
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => FALSE,
                'label' => 'admin.cars.list.image',
                'delete_label' => 'admin.removeImage',
                'download_label' => FALSE,
                'empty_data' => '',
                'attr' => [
                    'class' => 'stylizedUpload',
                    'data-labeltext' => $this->translator->trans('form.file.empty'),
                    'data-buttontext' => $this->translator->trans('form.file.browse'),
                ]
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
            'data_class' => Car::class,
        ]);
    }

    private function buildYearChoices()
    {
        $yearsBefore = date('Y', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 15));
        $yearsAfter = date('Y', mktime(0, 0, 0, date("m"), date("d"), date("Y") ));
        return array_combine(range($yearsBefore, $yearsAfter), range($yearsBefore, $yearsAfter));
    }
}