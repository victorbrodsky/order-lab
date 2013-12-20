<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Oleg\OrderformBundle\Entity\ProcedureList;

class ProcedureNameType extends AbstractType
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

//        if($this->params['type'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' || $this->params['cicle'] == 'edit' ) {    //|| $this->params['cicle'] == 'edit'
//            $attr = array('class' => 'ajax-combobox-procedure', 'type' => 'hidden');    //new
//            //echo "Procedure Type new <br>";
//        } else {
//            $attr = array('class' => 'form-control form-control-modif');    //show
//            //echo "Procedure Type show <br>";
//        }

        $attr = array('class' => 'ajax-combobox-procedure', 'type' => 'hidden');

        $builder->add('field', 'custom_selector', array(
            'label' => 'Procedure Type',
            'required' => false,
            'attr' => $attr,
            'classtype' => 'procedureType',
        ));


        $builder->add('procedureothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureName',
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureName',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_nametype';
    }
}
