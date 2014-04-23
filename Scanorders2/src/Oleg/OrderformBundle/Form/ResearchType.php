<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;

class ResearchType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        if( $this->params['type'] == 'SingleObject' ) {

            //data review: we need only edit primary pi and link principals to the existing User objects => all of this is inside of "ProjectTitleList" entity
            $builder->add( 'projectTitle', new ProjectTitleListType($this->params,$this->entity), array(
                'label'=>false
            ));

        } else {

            $builder->add( 'projectTitleStr', 'custom_selector', array(
                'label' => 'Research Project Title:',
                'required' => false,
                //'read_only' => $readonly,
                'attr' => array('class' => 'combobox combobox-width combobox-research-projectTitle', 'type' => 'hidden'),
                'classtype' => 'projectTitle'
            ));

            $builder->add( 'setTitleStr', 'custom_selector', array(
                'label' => 'Research Set Title:',
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width combobox-research-setTitle', 'type' => 'hidden'),
                //'read_only' => $readonly,
                'classtype' => 'setTitles'
            ));

            //$addlabel = " (as entered by user)";
            $builder->add('principalWrappers', 'custom_selector', array(
                'label' => 'Principal Investigator(s):',
                'attr' => array('class' => 'ajax-combobox-optionaluser-research', 'type' => 'hidden'),
                'required'=>false,
                'classtype' => 'optionalUserResearch'
            ));

        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Research'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_researchtype';
    }
}
