<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class ScanType extends AbstractType
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

        //scanregion
        $attr = array('class' => 'ajax-combobox-scanregion', 'type' => 'hidden');
        $options = array(
            'label' => 'Region to scan:',
            'max_length'=>500,
            'attr' => $attr,
            'classtype' => 'scanRegion'
        );
        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create') {
            $options['data'] = 'Entire Slide';
        }
        $builder->add('scanregion', 'custom_selector', $options);

        //note
        $builder->add('note', 'textarea', array(
                'max_length'=>5000,
                'required'=>false,
                'label'=>'Reason for Scan/Note:',
                'attr' => array('class'=>'textarea form-control'),   //form-control
        ));

        //abstract data
        $builder->add('scanothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Scan',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


        //mag
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $scan = $event->getData();
            $form = $event->getForm();

            $helper = new FormHelper();

            $tooltip =  "Scanning at 40X magnification is done Friday to Monday.".
                        "Some of the slides (about 7% of the batch) may have to be rescanned the following week in order to obtain sufficient image quality.".
                        "We will do our best to expedite the process.";

            $magArr = array(
                'label' => 'Magnification:',
                'choices' => $helper->getMags(),
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'attr' => array('class' => 'horizontal_type element-with-tooltip', 'required'=>'required', 'title'=>$tooltip)
            );

            // check if the Scan object is "new"
            if( !$scan || null === $scan->getId() ) {
                $magArr['data'] = '20X';
            }

            $form->add( 'field', 'choice', $magArr );

        });

        //        //mag
//        $helper = new FormHelper();
//        $magArr = array(
//            'label' => 'Magnification:',
//            'choices' => $helper->getMags(),
//            'required' => true,
//            'multiple' => false,
//            'expanded' => true,
//            'attr' => array('class' => 'horizontal_type', 'required'=>'required', 'title'=>'40X Scan Batch is run Fri-Mon. Some slide may have to be rescanned once or more. We will do our best to expedite the scanning.')
//        );
//        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' ) {
//            $magArr['data'] = '20X';    //new
//        }
//        $builder->add( 'field', 'choice', $magArr );


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Scan'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_scantype';
    }
}
