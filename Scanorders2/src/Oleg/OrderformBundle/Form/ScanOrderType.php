<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class ScanOrderType extends AbstractType
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

//        if( $this->params['cycle'] == 'show' ) {
//            //echo "entity service";
//            $builder->add('service', 'entity', array(
//                'label' => 'Service:',
//                'required'=> false,
//                'multiple' => false,
//                'class' => 'OlegUserdirectoryBundle:Service',
//                //'choices' => $this->params['services'],
//                'attr' => array('class' => 'combobox combobox-width')
//            ));
//        } else {
//            //service. User should be able to add institution to administrative or appointment titles
//            $builder->add('service', 'employees_custom_selector', array(
//                'label' => "Service:",
//                'required' => false,
//                'attr' => array('class' => 'combobox combobox-width ajax-combobox-service combobox-without-add', 'type' => 'hidden'),
//                'classtype' => 'service'
//            ));
//        }

        //Default Institution
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $title = $event->getData();
            $form = $event->getForm();

            $label = null;
            if( $title ) {
                $institution = $title->getScanOrderInstitutionScope();
                if( $institution ) {
                    $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
                }
            }
			if( !$label ) {
                $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
            }

            $form->add('scanOrderInstitutionScope', 'employees_custom_selector', array(
                //'label' => 'ScanOrder' . ' ' . $label . ' Scope' . ':',
                'label' => $label,
                'required' => false,

                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'ScanOrder',
                    'data-label-postfix' => 'Scope'
                ),
                'classtype' => 'institution'
            ));
        });

        //delivery
        $attr = array('class' => 'ajax-combobox-delivery', 'type' => 'hidden');
        $builder->add('delivery', 'custom_selector', array(
            'label' => 'Slide Delivery:',
            'attr' => $attr,
            'required'=>true,
            'classtype' => 'delivery'
        ));

        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ScanOrder'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_scanordertype';
    }
}
