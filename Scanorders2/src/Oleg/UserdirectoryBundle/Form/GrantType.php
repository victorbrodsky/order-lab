<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class GrantType extends AbstractType
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

//        $builder->add('id','hidden',array(
//            'label'=>false,
//            'attr' => array('class'=>'grant-id-field')
//        ));

        $builder->add('grantid',null,array(
            'label'=>'Grant ID Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('amount',null,array(
            'label'=>'Total Amount:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('startDate', 'date', array(
            'label' => "Grant Support Start Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

        $builder->add('endDate', 'date', array(
            'label' => "Grant Support End Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

        $builder->add('comment', 'textarea', array(
            'label'=>'Comment:',
            'required'=>false,
            'attr' => array('class' => 'textarea form-control')
        ));


        $builder->add('grantTitle', 'employees_custom_selector', array(
            'label'=>"Grant Title:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-granttitle', 'type' => 'hidden'),
            'classtype' => 'grantTitle'
        ));

        $builder->add('sourceOrganization', 'employees_custom_selector', array(
            'label' => "Source Organization:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-sourceorganization', 'type' => 'hidden'),
            'classtype' => 'sourceOrganization'
        ));

        $builder->add('grantLink', 'employees_custom_selector', array(
            'label' => "Link to a page with more information:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-grantlink', 'type' => 'hidden'),
            'classtype' => 'grantLink'
        ));

        $builder->add('effort', 'employees_custom_selector', array(
            'label' => 'Percent Effort:',
            'attr' => array('class' => 'ajax-combobox-effort', 'type' => 'hidden', "data-inputmask"=>"'mask': '[o]', 'repeat': 10, 'greedy' : false"),
            'required' => false,
            'classtype' => 'effort'
        ));



    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Grant',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_grant';
    }
}
