<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class EncounterInpatientinfoType extends AbstractType
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

        $builder->add('source', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            'label' => 'Inpatient Info Source System:',
            'required' => false,
            'data'  => null,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        //->where("list.name = 'WCMC Epic Ambulatory EMR' OR list.name = 'Written or oral referral'")
                        ->orderBy("list.orderinlist","ASC");

                },
        ));

        $builder->add('admissiondate','datetime',array(
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
            'required' => false,
            'label'=>'Admission Date:',
        ));

        $builder->add('admissiontime', 'time', array(
            'input'  => 'datetime',
            'widget' => 'choice',
            'label'=>'Admission Time:'
        ));

        $builder->add('admissiondiagnosis',null,array(
            'required' => false,
            'label'=>'Diagnosis on Admission:',
            //'attr' => array('class' => 'form-control'),
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('dischargedate','datetime',array(
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
            'required' => false,
            'label'=>'Discharge Date:',
        ));

        $builder->add('dischargetime', 'time', array(
            'input'  => 'datetime',
            'widget' => 'choice',
            'label'=>'Discharge Time:'
        ));

        $builder->add('dischargediagnosis',null,array(
            'required' => false,
            'label'=>'Diagnosis on Discharge:',
            'attr' => array('class'=>'textarea form-control')
        ));


        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterInpatientinfo',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterInpatientinfo',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encounterinpatientinfotype';
    }
}
