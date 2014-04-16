<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/14/14
 * Time: 1:09 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;

class ProjectTitleListType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        $addlabel = "";
//        $readonly = false;
//
//        if( $this->params['type'] == 'SingleObject' ) {
//            //this is used by data review, when a single onject is shown
//            $attr = array('class'=>'form-control form-control-modif');
//            $addlabel = " (as entered by user)";
//            $readonly = true;
//        } else {
//            //this is used by orderinfo form, when the scan order form is shown ($this->params['type']="Multi-Slide Scan Order")
//            $attr = array('class' => 'ajax-combobox-optionaluser-research', 'type' => 'hidden');
//        }

        $builder->add( 'name', 'custom_selector', array(
            'label' => 'Research Project Title:',
            'required' => false,
            //'read_only' => $readonly,
            'attr' => array('class' => 'combobox combobox-width combobox-research-projectTitle', 'type' => 'hidden'),
            'classtype' => 'projectTitle'
        ));

        $builder->add( 'setTitles', 'custom_selector', array(
            'label' => 'Research Set Title:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width combobox-research-setTitle', 'type' => 'hidden'),
            //'read_only' => $readonly,
            'classtype' => 'setTitles'
        ));

        $builder->add('principals', new PrincipalType($this->params, null), array(
            'data_class' => null,   //'Oleg\OrderformBundle\Entity\PIList',
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProjectTitleList'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_projecttitlelisttype';
    }
}
