<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class StainType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        $helper = new FormHelper();
//        $builder->add('name', 'choice', array(
//            'choices' => $helper->getStains(),
//            'data' => 0,
//            'max_length' => 200,
//            'required' => true,
//            'label' => '* Stain:',
//            'attr' => array('class' => 'combobox combobox-width', 'required' => 'required', 'disabled')
////            'attr' => array('class'=>'select2combo','required' => 'required')
//        ));

        $builder->add('name', null, array(
            'label' => '* Stain:',
            'required' => true,
            'attr' => array('class' => 'combobox combobox-width')
        ));
        
//        $builder->add('name', 'hidden', array(
//            'label' => '* Stain:',
//            //'required' => true,
//            'attr' => array('class' => 'combobox combobox-width', 'type' => 'hidden')
//        ));


        $factory  = $builder->getFormFactory();
        $builder->addEventListener( FormEvents::PRE_SET_DATA, function(FormEvent $event) use($factory){

            $form = $event->getForm();
            $data = $event->getData();

//            echo "class=".get_class($data)."<br>";
//            echo "parent=".get_parent_class($data)."<br>";

            if(0) {
            //if( get_parent_class($data) == 'Oleg\OrderformBundle\Entity\Stain' || get_class($data) == 'Oleg\OrderformBundle\Entity\Stain' ) {

                $name = $data->getName();
                //echo "name === ".$name;

                $helper = new FormHelper();
                $arr = $helper->getStains();
                //echo "stain count = " . count($arr);
                //exit;

                $param = array(
                    'choices' => $arr,
                    'max_length' => 200,
                    'required' => true,
                    'label' => '* Stain:',
                    'attr' => array('class' => 'combobox combobox-width', 'required' => 'required', 'disabled' ),
//                        'attr' => array('required' => 'required'),
                    'auto_initialize' => false,
                );

                $counter = 0;
                $key = 0;
                foreach( $arr as $var ){
                    //echo "<br>".$var."?".$name;
                    if( trim( $var ) == trim( $name ) ){
                        $key = $counter;
                        //echo " key=".$key;
                        //$param['data'] = $key;
                        break;
                    }
                    $counter++;
                }
                $param['data'] = $key;

                 $form->add(
                     $factory->createNamed(
                        'name',
                        'choice',
                        null,
                        $param
                 ));

            }

        });

        
//        $builder->add('stainer', 'text', array(
//            'label'=>'Stainer:',
//            'max_length'=>200,
//            'required'=>false
//        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Stain'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_staintype';
    }
}
