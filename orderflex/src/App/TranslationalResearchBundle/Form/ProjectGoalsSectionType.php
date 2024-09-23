<?php

namespace App\TranslationalResearchBundle\Form;

use App\TranslationalResearchBundle\Entity\OrderableStatusList; //process.py script: replaced namespace by ::class: added use line for classname=OrderableStatusList
use App\TranslationalResearchBundle\Entity\RequestCategoryTypeList;
use App\TranslationalResearchBundle\Util\TransResUtil;
use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProjectGoalsSectionType extends AbstractType
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

        //Used for Work Request page
        //Show this field on “Work Request View” page to all users only if this field is non-empty
        //Show this field on “Work Request Edit” page to users with TRP roles other than “basic TRP submitter”, even if it is empty on this Edit page
        if( $this->params['cycle'] == 'new' || $this->params['cycle'] == 'edit' || $this->params['cycle'] == 'show' ) {
            //echo "cycle=".$this->params['cycle']."<br>";
            $builder->add('projectGoals', CollectionType::class, array(
                'entry_type' => ProjectGoalType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params
                ),
                'label' => false,
                //'disabled' => true,
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__projectgoals__',
            ));

//            $builder->add('save', SubmitType::class, array(
//                'label' => 'Save Project Goals',
//                'attr' => array('class' => 'btn btn-warning')
//            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\TranslationalResearchBundle\Entity\Project',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'oleg_translationalresearchbundle_projectgoal';
    }


}
