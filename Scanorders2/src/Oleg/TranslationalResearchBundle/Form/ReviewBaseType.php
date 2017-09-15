<?php

namespace Oleg\TranslationalResearchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

//IrbReviewType
class ReviewBaseType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add( 'id', HiddenType::class, array(
            'label'=>false,
            'required'=>false,
            //'attr' => array('class' => 'comment-field-id')
        ));

        //echo "add reviewer object <br>";

        //$builder->add('assignment')->add('createdate')->add('updatedate')->add('decision')->add('comment')->add('project')->add('reviewer')->add('reviewerDelegate');

        $approved = 'Approved';
        $rejected = 'Rejected';
        if( $this->params["stateStr"] == "committee_review" ) {
            $approved = 'Like';
            $rejected = 'Dislike';
        }

        $builder->add('decision', ChoiceType::class, array(
            'choices' => array(
                $approved => 'approved',
                $rejected => 'rejected'
            ),
            'invalid_message' => 'invalid value: decision',
            //'choices_as_values' => true,
            'label' => "Decision:",
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type')
        ));

        $builder->add('comment', TextareaType::class, array(
            'label'=>'Comment:',
            'required'=> false,
            'attr' => array('class'=>'textarea form-control'),
        ));

        $builder->add('reviewer', null, array(
            'label' => "Reviewer:",
            //'disabled' => true,
            'attr' => array('class'=>'combobox combobox-width') //, 'readonly'=>true
        ));

        $builder->add('reviewerDelegate', null, array(
            'label' => "Reviewer Delegate:",
            //'disabled' => true,
            'attr' => array('class'=>'combobox combobox-width') //, 'readonly'=>true
        ));


    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_review';
    }


}
