<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class ResearchLabType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if( strpos($this->params['cycle'],'_standalone') === false ) {
            $readonly = true;
        } else {
            $readonly = false;
        }

        //echo "cycle=".$this->params['cycle']."<br>";

        $builder->add( 'id', 'hidden', array(
            'label' => false,
            'attr' => array('class' => 'researchlab-id-field')
        ));

        $builder->add('foundedDate','date',array(
            'read_only' => $readonly,
            'label'=>"Founded on:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control researchlab-foundedDate-field')
        ));

        $builder->add('dissolvedDate','date',array(
            'read_only' => $readonly,
            'label'=>"Dissolved on:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control user-expired-end-date researchlab-dissolvedDate-field')
        ));

        $builder->add('location', 'employees_custom_selector', array(
            'read_only' => $readonly,
            'label' => "Location:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-location', 'type' => 'hidden'),
            'classtype' => 'location'
        ));

        $builder->add('weblink', null, array(
            'read_only' => $readonly,
            'label' => 'Web page link:',
            'attr' => array('class'=>'form-control researchlab-weblink-field')
        ));

        //Consider stanAlone for all cycles with _standalone, except new_standalone. Cycle new_standalone is exception because we don't show list attributes in creation page
        if( strpos($this->params['cycle'],'_standalone') !== false && strpos($this->params['cycle'],'new') === false ) {
            //list attributes
            $params = array();
            $mapper = array();
            $params['user'] = $this->params['user'];
            $params['cycle'] = $this->params['cycle'];
            $params['standalone'] = true;
            $mapper['className'] = "ResearchLab";
            $mapper['bundleName'] = "OlegUserdirectoryBundle";

            $builder->add('list', new ListType($params, $mapper), array(
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\ResearchLab',
                'label' => false
            ));
        }

        //echo "subjectUser=".$this->params['subjectUser']."<br>";

        if( strpos($this->params['cycle'],'_standalone') === false ) {

            ////////////////////////// comment and pi /////////////////////////
            //pi and comment
            //pi and common are arrays, but we should show only objects belonging to the subjectUser,
            //so we relay only on dummy variables and set them according to the current lab
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

                $lab = $event->getData();
                $form = $event->getForm();

                $form->add('name', 'employees_custom_selector', array(
                    'read_only' => ($lab && $lab->getId() ? true : false),
                    'label' => "Research Lab Title:",
                    'required' => false,
                    'attr' => array('class' => 'combobox combobox-width ajax-combobox-researchlab', 'type' => 'hidden'),
                    'classtype' => 'researchlab'
                ));

                if( $lab ) {

                    foreach( $lab->getComments() as $comment ) {
                        if( $comment->getAuthor() && $comment->getAuthor()->getId() == $this->params['subjectUser']->getId() ) {

                            //preset comment dummy for current lab
                            $lab->setCommentDummy($comment->getComment());

    //                        $form->add('comments', 'collection', array(
    //                            'type' => new ResearchLabCommentType($this->params),
    //                            'label' => false,
    //                            'required' => false,
    //                            'allow_add' => true,
    //                            'allow_delete' => true,
    //                            'by_reference' => false,
    //                            'prototype' => true,
    //                            'prototype_name' => '__comments__',
    //                        ));
                        }
                    }

                    foreach( $lab->getPis() as $pi ) {
                        if( $pi && $pi == true && $pi->getPi()->getId() == $this->params['subjectUser']->getId() ) {

                            //preset pi dummy for current lab
                            $lab->setPiDummy(true);

    //                        $form->add('pis', 'collection', array(
    //                            'type' => new ResearchLabPIType($this->params),
    //                            'label' => false,
    //                            'required' => false,
    //                            'allow_add' => true,
    //                            'allow_delete' => true,
    //                            'by_reference' => false,
    //                            'prototype' => true,
    //                            'prototype_name' => '__comments__',
    //                        ));
                        }
                    }

                }


            });

            $builder->add('commentDummy','textarea',array(
                //'mapped' => false,
                'required' => false,
                'label'=>'Comment:',
                'attr' => array('class'=>'textarea form-control researchlab-commentDummy-field')
            ));

            $builder->add('piDummy', 'checkbox', array(
                //'mapped' => false,
                'required' => false,
                'label' => 'Principal Investigator of this Lab:',
                'attr' => array('class'=>'form-control researchlab-piDummy-field', 'style'=>'margin:0')
            ));

            ////////////////////////// EOF comment and pi /////////////////////////

        } else {

            //use name as lab unique identifier
            $builder->add('name',null,array(
                'label'=>"Research Lab Title:",
                'required' => true,
                'attr' => array('class' => 'form-control')
            ));

        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\ResearchLab',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_researchlab';
    }
}
