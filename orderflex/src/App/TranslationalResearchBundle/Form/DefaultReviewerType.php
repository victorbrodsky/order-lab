<?php

namespace App\TranslationalResearchBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class DefaultReviewerType extends AbstractType
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

        //$builder->add('createDate')->add('updateDate')->add('state')->add('creator')->add('updateUser')->add('reviewer')->add('reviewerDelegate');

        if( $this->params['showPrimaryReview'] ) {
            //echo "show primaryReview <br>";
            $builder->add('primaryReview', CheckboxType::class, array(
                'label' => 'Primary Review:',
                'required' => false,
                'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
            ));
        }


        $builder->add( 'reviewer', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> "Reviewer:",
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder()
        ));

        $builder->add( 'reviewerDelegate', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> "Reviewer Delegate:",
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder()
        ));

        $builder->add( 'projectSpecialty', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:SpecialtyList',
            'choice_label' => 'name',
            'label'=>'Project Specialty:',
            'disabled' => true, //($this->params['admin'] ? false : true),
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

//        if( $this->params['showFunded'] ) {
//            $builder->add('funded', CheckboxType::class, array(
//                'required' => false,
//                'label' => "Review for Funded Projects:",
//                'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
//            ));
//        }
        if( $this->params['showProjectType'] ) {
            $builder->add('reviewProjectType', ChoiceType::class, array(
                'choices'   => array(
                    'All' => 'all',
                    'Funded' => 'funded',
                    'Non-Funded' => 'non-funded'
                ),
                'label' => "Review for Projects:",
                'required' => true,
                'attr' => array('class' => 'combobox combobox-no-width other-status'),
            ));
        }

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\TranslationalResearchBundle\Entity\DefaultReviewer',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'oleg_translationalresearchbundle_defaultreviewer';
    }


}
