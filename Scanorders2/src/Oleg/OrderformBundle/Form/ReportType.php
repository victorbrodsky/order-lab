<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\DocumentContainerType;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Oleg\UserdirectoryBundle\Form\UserWrapperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ReportType extends AbstractType
{

    protected $params;
    protected $entity;
    protected $label;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        //////////// create labels ////////////
        $label = array();
        $label['processedDate'] = "Processed Date:";
        $label['processedByUser'] = "Processed By:";

        $dataEntity = $this->params['dataEntity'];

        //slide report
        if( $dataEntity->getMessageCategory() && $dataEntity->getMessageCategory()->getName() == "Slide Report" ) {
            $label['processedDate'] = "Slide Cut or Prepared On:";
            $label['processedByUser'] = "Slide Cut or Prepared By:";
        }

        $this->label = $label;
        //////////// EOF create labels ////////////
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('issuedDate', 'date', array(
            'label' => "Issued Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('receivedDate', 'date', array(
            'label' => "Received Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('signatureDate', 'date', array(
            'label' => "Signature Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('processedDate', 'date', array(
            'label' => $this->label['processedDate'], //"Processed Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('processedByUser', null, array(
            'label' => $this->label['processedByUser'], //'Processed By:',
            'attr' => array('class' => 'combobox combobox-width'),
        ));

//        $builder->add('reportType', null, array(
//            'label' => "Report Type:",
//            'required' => false,
//            'multiple' => false,
//            'attr' => array('class'=>'combobox combobox-width'),
//        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Report',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_reporttype';
    }
}
