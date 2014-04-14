<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/14/14
 * Time: 1:09 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;

class ProjectTitleListType extends AbstractType
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

        $builder->add( 'name', 'custom_selector', array(
            'label' => 'Research Project Title:',
            'required' => false,
            //'read_only' => $readonly,
            'attr' => array('class' => 'combobox combobox-width combobox-research-projectTitle', 'type' => 'hidden'),
            'classtype' => 'projectTitle'
        ));

        $builder->add( 'setTitles', 'custom_selector', array(
            'label' => 'Research Set Title:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width combobox-research-setTitle', 'type' => 'hidden'),
            'read_only' => $readonly,
            'classtype' => 'setTitles'
        ));


//        $formModifier = function( FormInterface $form, $project = null ) { //FormInterface
//
//            if( $project ) {
//                $settitles = $project->getSetTitles();
//                $form->add('setTitles', 'entity', array(
//                    'class'       => 'OlegOrderformBundle:SetTitleList',
//                    'empty_value' => '',
//                    'choices'     => $settitles,
//                ));
//            } else {
//                $form->add( 'setTitles', 'custom_selector', array(
//                    'label'=>'Research Set Title:',
//                    'required'=> false,
//                    'attr' => array('class' => 'combobox combobox-width combobox-research-setTitle', 'type' => 'hidden'),
//                    //'read_only' => $readonly,
//                    'classtype' => 'setTitles'
//                ));
//            }
//
//        };
//
//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) use ($formModifier) {
//                // this would be your entity, i.e. SportMeetup
//                $data = $event->getData();
//                echo "data=".$data."<br \>";
//                $formModifier( $event->getForm(), $data );
//            }
//        );
//
//        $builder->get('name')->addEventListener(
//            FormEvents::POST_SUBMIT,
//            function (FormEvent $event) use ($formModifier) {
//                // It's important here to fetch $event->getForm()->getData(), as
//                // $event->getData() will get you the client data (that is, the ID)
//                $sport = $event->getForm()->getData();
//
//                // since we've added the listener to the child, we'll have to pass on
//                // the parent to the callback functions!
//                $formModifier($event->getForm()->getParent(), $sport);
//            }
//        );

//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) {
//                $form = $event->getForm();
//
//                // this would be your entity, i.e. Project Title
//                $data = $event->getData();
//
//                $project = $data->getProjectTitle();
//
//                echo "project=".$project."<br/>";
//
//                $positions = null === $project ? array() : $project->getSetTitle();
//
//                $form->add('setTitle', 'custom_selector', array(
//                    'attr' => array('class' => 'combobox combobox-width combobox-research-setTitle', 'type' => 'hidden'),
//                    'label' => 'Research Set Title:',
//                    //'choices' => $positions,
//                    'classtype' => 'setTitle',
//                ));
//            }
//        );

        //////////////////// EOF project->title ////////////////////


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProjectTitleListType'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_projecttitleListtype';
    }
}
