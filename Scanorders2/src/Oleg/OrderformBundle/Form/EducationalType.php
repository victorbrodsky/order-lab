<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

//use Oleg\OrderformBundle\Helper\FormHelper;

class EducationalType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        if( $this->params['type'] == 'SingleObject' ) {

            //data review: we need only edit primary pi and link principals to the existing User objects => all of this is inside of "CourseTitleList" entity
            $builder->add( 'courseTitle', new CourseTitleListType($this->params,$this->entity), array(
                'label'=>false
            ));

        } else {

            $builder->add( 'courseTitleStr', 'custom_selector', array(
                'label' => 'Course Title:',
                'required' => false,
                //'read_only' => $readonly,
                'attr' => array('class' => 'combobox combobox-width combobox-educational-courseTitle', 'type' => 'hidden'),
                'classtype' => 'projectTitle'
            ));

            $builder->add( 'lessonTitleStr', 'custom_selector', array(
                'label' => 'Lesson Title:',
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width combobox-educational-lessonTitle', 'type' => 'hidden'),
                //'read_only' => $readonly,
                'classtype' => 'setTitles'
            ));

            //$addlabel = " (as entered by user)";
            $builder->add('directorWrappers', 'custom_selector', array(
                'label' => 'Course Director(s):',
                'attr' => array('class' => 'ajax-combobox-optionaluser-educational', 'type' => 'hidden'),
                'required'=>false,
                'classtype' => 'optionalUserEducational'
            ));

        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Educational'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_educationaltype';
    }
}
