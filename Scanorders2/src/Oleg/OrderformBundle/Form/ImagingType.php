<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\DocumentContainerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;

use Oleg\OrderformBundle\Helper\FormHelper;

class ImagingType extends AbstractType
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
        if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create') {
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
        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Imaging',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


        ///////////// mag /////////////
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
        ///////////// EOF mag /////////////

        if( array_key_exists('datastructure', $this->params) &&  $this->params['datastructure'] == true ) {

            $builder->add('imageId', 'text', array(
                'required'=>false,
                'label'=>'Image ID:',
                'attr' => array('class'=>'form-control'),
            ));

//            $builder->add('imageLink', 'text', array(
//                'required'=>false,
//                'label'=>'Image Link:',
//                'attr' => array('class'=>'form-control'),
//            ));

            $builder->add('source', null, array(
                'required'=>false,
                'label'=>'Image ID Source System:',
                'attr' => array('class' => 'combobox combobox-width'),
            ));

//            $builder->add('provider', null, array(
//                'required'=>false,
//                'label'=>'Image Acquired By:',
//                'attr' => array('class' => 'combobox combobox-width'),
//            ));

//            $builder->add('creationdate','date',array(
//                'widget' => 'single_text',
//                'format' => 'MM/dd/yyyy, H:mm:ss',
//                'attr' => array('class' => 'datepicker form-control'),
//                'required' => false,
//                'label'=>'Image Acquisition Date & Time:',
//            ));

//            $builder->add( 'equipment', 'entity', array(
//                'class' => 'OlegUserdirectoryBundle:Equipment',
//                'property' => 'name',
//                'label' => 'Image Acquisition Device:',
//                'required'=> true,
//                'multiple' => false,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('i')
//                            ->leftJoin('i.keytype','keytype')
//                            ->where("keytype.name = :keytype AND i.type != :type")
//                            ->setParameters( array('keytype' => 'Whole Slide Scanner', 'type' => 'disabled') );
//                    },
//            ));

            //Image container
            $params = array('labelPrefix'=>'Acquired Image');
            $equipmentTypes = array('Whole Slide Scanners','Microscope Camera');
            $params['device.types'] = $equipmentTypes;
            //$params['document.device'] = false;
            //$params['document.datetime'] = false;
            //$params['document.provider'] = false;
            $params['document.device.label'] = 'Image Acquisition Device:';
            $params['document.datetime.label'] = 'Image Acquisition Date and Time:';
            $params['document.provider.label'] = 'Image acquired by:';
            $params['document.link.label'] = 'Image Link:';
            $builder->add('documentContainer', new DocumentContainerType($params), array(
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\DocumentContainer',
                'label' => false
            ));

        }


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Imaging'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_imagingtype';
    }
}
