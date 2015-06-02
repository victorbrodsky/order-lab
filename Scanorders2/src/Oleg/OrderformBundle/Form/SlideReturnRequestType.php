<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class SlideReturnRequestType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;

        $labels = array(
            'institution' => 'Institution:',
            'destinations.location' => 'Return Slides to:',
        );

        $this->params['labels'] = $labels;
    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->params['slide'] = true;
        $builder->add('message', new MessageObjectType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Message',
            'label' => false
        ));



        $builder->add('urgency', 'custom_selector', array(
            'label' => 'Urgency:',
            'attr' => array('class' => 'ajax-combobox-urgency', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'urgency'
        ));


        if( array_key_exists('type', $this->params) &&  $this->params['type'] == 'table' ) {

            //echo "type=table <br>";

            $builder->add('returnoption', 'checkbox', array(
                'label'     => 'Return all slides that belong to listed accession numbers:',
                'required'  => false,
            ));

            $builder->add('datalocker','hidden', array(
                'mapped' => false,
                'label' => false,
                'attr' => array('class' => 'slidereturnrequest-datalocker-field')
                //'required'  => false,
            ));

        }


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\SlideReturnRequest'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_slidereturnrequesttype';
    }
}
