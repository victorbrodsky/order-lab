<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 8/11/14
 * Time: 3:56 PM
 */


namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RelevantScansType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('field', null, array(
            'label' => 'Link(s) to related image(s)',
            'required' => false,
            'attr' => array('class'=>'form-control sliderelevantscan-field', 'style'=>'height: 35px;')
            //'attr' => array('class'=>'form-control')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\RelevantScans'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_relevantscanstype';
    }
}