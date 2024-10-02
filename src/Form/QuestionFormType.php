<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder -> add('answers', ChoiceType::class, [

            'label' => 'Варианты ответа',
            'error_bubbling' => true,
            'expanded' => true,
            'multiple' => true,
            'choices' => $options['answers']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver -> setDefaults([
          'answers' => []
        ]);
    }
}