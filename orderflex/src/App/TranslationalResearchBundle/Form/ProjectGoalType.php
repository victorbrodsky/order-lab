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

//Similar to ProductType
//Show on “Project Request Edit” page for Platform Admin/Deputy Platform Admin / TRP Admin / TRP Tech

class ProjectGoalType extends AbstractType
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

        //For collection with "Add New ..." must add id to correctly calculate ned section id
        $builder->add('id', HiddenType::class, array(
            'attr' => array('class'=>'projectgoals-id'),
        ));

        $builder->add('description', null, array(
            'label' => "Project Goal:",
            'required' => false,
            //'disabled' => $this->disabled,
            'attr' => array('class' => 'textarea form-control projectgoal-description')
        ));

//        $builder->add('status', null, array(
//            'label' => "Status:",
//            'required' => false,
//            //'disabled' => $this->disabled,
//            'attr' => array('class' => 'form-control projectgoal-status')
//        ));
        $builder->add('status', ChoiceType::class, array(
            'label' => "Status:",
            'choices' => array(
                "Enable" => "enable",
                "Disable" => "disable",
            ),
            'data' => 'enable',
            'required' => false,
            'attr' => array('class' => 'combobox', 'placeholder' => "Status")
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\TranslationalResearchBundle\Entity\ProjectGoal',
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
