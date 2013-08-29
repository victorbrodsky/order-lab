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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $helper = new FormHelper();
        
        //$builder->add( 'status', 'hidden', array('data' => 'submitted') ); 
        
        $builder->add('scanregion', 'text', array(
                'max_length'=>200,
                'required'=>false,
                'label' => 'Region to scan',
                'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'mag', 
                'choice', array(  
                'label' => 'Magnification:',
                'max_length'=>50,
                'choices' => $helper->getMags(),
                'required' => true,
                'data' => 0,//'20X',
                'multiple' => false,
                'expanded' => true,
                'attr' => array('class' => 'horizontal_type', 'required' => 'required', 'title'=>'40X Scan Batch is run Fri-Mon. Some slide may have to be rescanned once or more. We will do our best to expedite the scanning.')
        ));                       
        
        $builder->add('note', 'textarea', array(
                'max_length'=>5000,
                'required'=>false,
                'label'=>'Reason for Scan/Note:',
                //'data' => 'Interesting case',
                'attr' => array('class'=>'form-control'),
        ));


        //use listener or pass actual entity to each entity type
        $factory  = $builder->getFormFactory();
        $builder->addEventListener( FormEvents::PRE_SET_DATA, function(FormEvent $event) use($factory){

                $form = $event->getForm();
                $data = $event->getData();

                //echo "class=".get_class($data)."<br>";
                //echo "parent=".get_parent_class($data)."<br>";

//                if( get_parent_class($data) == 'Oleg\OrderformBundle\Entity\Scan' ) {
                if( get_parent_class($data) == 'Oleg\OrderformBundle\Entity\Scan' || get_class($data) == 'Oleg\OrderformBundle\Entity\Scan' ) {

                    $name = $data->getMag();
                    //echo "name === ".$name;

                    $helper = new FormHelper();
                    $arr = $helper->getMags();

                    $param = array(
                            'label' => 'Magnification:',
                            'max_length'=>50,
                            'choices' => $arr,
                            'required' => true,
                            'multiple' => false,
                            'expanded' => true,
                            'auto_initialize' => false,
                            'attr' => array(
                                            'class' => 'horizontal_type',
                                            'required' => 'required',
                                            'title'=>'40X Scan Batch is run Fri-Mon. Some slide may have to be rescanned once or more. We will do our best to expedite the scanning.'
                                           )
                        );

                    $counter = 0;
                    $key = 0;
                    foreach( $arr as $var ){
                        //echo "<br>".$var."?".$name;
                        if( trim( $var ) == trim( $name ) ){
                            $key = $counter;
                            //echo " key=".$key;
                            break;
                        }
                        $counter++;
                    }
                    $param['data'] = $key;

                    $form->add(
                        $factory->createNamed(
                            'mag',
                            'choice',
                            null,
                            $param
                        ));

                }

            }
        );



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
