<?php

namespace Oleg\TranslationalResearchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


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

        $disabledReviewers = true;
        if( $this->params['admin'] ) {
            $disabledReviewers = false;
        }

        $builder->add( 'id', HiddenType::class, array(
            'label'=>false,
            'required'=>false,
            //'attr' => array('class' => 'comment-field-id')
        ));

        //echo "add reviewer object <br>";

        //$builder->add('assignment');

        $builder->add('reviewer', null, array(
            'label' => "Reviewer:",
            'disabled' => $disabledReviewers,
            'attr' => array('class'=>'combobox combobox-width') //, 'readonly'=>true
        ));

        $builder->add('reviewerDelegate', null, array(
            'label' => "Reviewer Delegate:",
            'disabled' => $disabledReviewers,
            'attr' => array('class'=>'combobox combobox-width') //, 'readonly'=>true
        ));

        if( 1 ) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

                $reviewEntity = $event->getData();
                $form = $event->getForm();

                if (!$reviewEntity) {
                    return null;
                }

                $disabledReviewerFields = true;
                if ($this->params['admin']) {
                    $disabledReviewerFields = false;
                }
                if ($this->params['user']->getId() == $reviewEntity->getReviewer()->getId()) {
                    $disabledReviewerFields = false;
                }
                if (
                    $reviewEntity->getReviewerDelegate() &&
                    $this->params['user']->getId() == $reviewEntity->getReviewerDelegate()->getId()
                ) {
                    $disabledReviewerFields = false;
                }

                //Reviewer's field
                $approved = 'Approved';
                $rejected = 'Rejected';
                if ($this->params["stateStr"] == "committee_review") {
                    $approved = 'Like';
                    $rejected = 'Dislike';
                }

                $form->add('decision', ChoiceType::class, array(
                    'choices' => array(
                        $approved => 'approved',
                        $rejected => 'rejected',
                        'Pending' => null
                    ),
                    'invalid_message' => 'invalid value: decision',
                    //'choices_as_values' => true,
                    'disabled' => $disabledReviewerFields,
                    'label' => "Decision:",
                    'multiple' => false,
                    'expanded' => true,
                    'attr' => array('class' => 'horizontal_type')
                ));

                $form->add('comment', TextareaType::class, array(
                    'label' => 'Comment:',
                    'disabled' => $disabledReviewerFields,
                    'required' => false,
                    'attr' => array('class' => 'textarea form-control'),
                ));

//                $form->add('reviewedBy', null, array(
//                    'label' => "Reviewed By:",
//                    'disabled' => true,
//                    'attr' => array('class'=>'combobox combobox-width') //, 'readonly'=>true
//                ));

            });
        } else {
            //Reviewer's field
            $approved = 'Approved';
            $rejected = 'Rejected';
            if ($this->params["stateStr"] == "committee_review") {
                $approved = 'Like';
                $rejected = 'Dislike';
            }

            $builder->add('decision', ChoiceType::class, array(
                'choices' => array(
                    $approved => 'approved',
                    $rejected => 'rejected',
                    'Pending' => null
                ),
                'invalid_message' => 'invalid value: decision',
                //'choices_as_values' => true,
                //'disabled' => $disabledReviewerFields,
                'label' => "Decision:",
                'multiple' => false,
                'expanded' => true,
                'attr' => array('class' => 'horizontal_type')
            ));

            $builder->add('comment', TextareaType::class, array(
                'label' => 'Comment:',
                //'disabled' => $disabledReviewerFields,
                'required' => false,
                'attr' => array('class' => 'textarea form-control'),
            ));
        }

//        //Reviewer's field
//        $approved = 'Approved';
//        $rejected = 'Rejected';
//        if( $this->params["stateStr"] == "committee_review" ) {
//            $approved = 'Like';
//            $rejected = 'Dislike';
//        }
//
//        $builder->add('decision', ChoiceType::class, array(
//            'choices' => array(
//                $approved => 'approved',
//                $rejected => 'rejected'
//            ),
//            'invalid_message' => 'invalid value: decision',
//            //'choices_as_values' => true,
//            'disabled' => $disabledReviewerFields,
//            'label' => "Decision:",
//            'multiple' => false,
//            'expanded' => true,
//            'attr' => array('class' => 'horizontal_type')
//        ));
//
//        $builder->add('comment', TextareaType::class, array(
//            'label'=>'Comment:',
//            'disabled' => $disabledReviewerFields,
//            'required'=> false,
//            'attr' => array('class'=>'textarea form-control'),
//        ));




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
