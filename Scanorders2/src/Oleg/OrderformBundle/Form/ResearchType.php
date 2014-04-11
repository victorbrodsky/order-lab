<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;

class ResearchType extends AbstractType
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

        $addlabel = "";
        $readonly = false;

        if( $this->params['type'] == 'SingleObject' ) {
            //this is used by data review, when a single onject is shown
            $attr = array('class'=>'form-control form-control-modif');
            $addlabel = " (as entered by user)";
            $readonly = true;
        } else {
            //this is used by orderinfo form, when the scan order form is shown ($this->params['type']="Multi-Slide Scan Order")
            $attr = array('class' => 'ajax-combobox-optionaluser-research', 'type' => 'hidden');
        }
        
//        $builder->add( 'projectTitle', 'text', array(
//            'label'=>'Research Project Title:',
//            'max_length'=>'500',
//            'required'=> false,
//            'attr' => array('class'=>'form-control form-control-modif'),
//            'read_only' => $readonly
//        ));
//
//        $builder->add( 'setTitle', 'text', array(
//            'label'=>'Research Set Title:',
//            'max_length'=>'500',
//            'required'=> false,
//            'attr' => array('class'=>'form-control form-control-modif'),
//            'read_only' => $readonly
//        ));

        //////////////////// project->title ////////////////////
        //http://symfony.com/doc/current/cookbook/form/dynamic_form_modification.html#cookbook-form-events-submitted-data

        $builder->add('projectTitle', 'custom_selector', array(
            'attr' => array('class' => 'combobox combobox-width combobox-research-projectTitle', 'type' => 'hidden'),
            'label' => 'Research Project Title:',
            'classtype' => 'projectTitle',
            //'choices' => array("1"=>"1","2"=>"2"),
        ));

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();

                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                $project = $data->getProjectTitle();
                $positions = null === $project ? array() : $project->getSetTitle();

                $form->add('setTitle', 'custom_selector', array(
                    'attr' => array('class' => 'combobox combobox-width combobox-research-setTitle', 'type' => 'hidden'),
                    'label' => 'Research Set Title:',
                    //'choices' => $positions,
                    'classtype' => 'setTitle',
                ));
            }
        );
        //////////////////// EOF project->title ////////////////////


        //principal
        $builder->add('principalstr', 'custom_selector', array(
            'label' => 'Principal Investigator'.$addlabel.':',
            'attr' => $attr,
            'required'=>false,
            'classtype' => 'optionalUserResearch',
            'read_only' => $readonly
        ));

        if( $this->params['type'] == 'SingleObject' ) {

            $attr = array('class' => 'combobox combobox-width');
            $builder->add('principal', 'entity', array(
                'class' => 'OlegOrderformBundle:User',
                'label'=>'Principal Investigator:',
                'required' => false,
                //'read_only' => true,    //not working => disable by twig
                //'multiple' => true,
                'attr' => $attr,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.locked=:locked')
                        ->setParameter('locked', '0');
                },
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
