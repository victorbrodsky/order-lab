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

class CourseTitleListType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'name', 'custom_selector', array(
            'label' => 'Course Title:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width combobox-educational-courseTitle', 'type' => 'hidden'),
            'classtype' => 'courseTitle'
        ));

        $builder->add( 'lessonTitles', 'custom_selector', array(
            'label' => 'Lesson Title:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width combobox-educational-lessonTitle', 'type' => 'hidden'),
            'classtype' => 'lessonTitles'
        ));

        ///////////////////// Director //////////////////////////////////

        $addlabel = "";
        $readonly = false;

        //echo "type=".$this->params['type']."<br>";

        if( $this->params['type'] == 'SingleObject' ) {
            //this is used by data review, when a single onject is shown
            $attr = array('class'=>'form-control form-control-modif');
            $addlabel = " (as entered by user)";
            $readonly = true;

            //show a user object linked to the educational. Show it only for data review.
            if( $this->params['type'] == 'SingleObject' ) {
                $attr = array('class' => 'combobox combobox-width');
                $builder->add('directors', 'entity', array(
                    'class' => 'OlegOrderformBundle:DirectorList',
                    'label'=>'Course Director:',
                    'required' => false,
                    'multiple' => true,
                    'attr' => $attr,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('list')
                            ->leftJoin("list.courses","parents")
                            ->where("parents.id = :id")
                            ->setParameter('id', $this->entity->getId());
                    },
                ));
            }

        } else {

            //this is used by orderinfo form, when the scan order form is shown ($this->params['type']="Multi-Slide Scan Order")
            $attr = array('class' => 'ajax-combobox-optionaluser-educational', 'type' => 'hidden');
            $builder->add('directors', 'custom_selector', array(
                'label' => 'Course Director:',
                'attr' => $attr,
                'required'=>false,
                'classtype' => 'optionalUserEducational'
            ));

        }





        ///////////////////////////// EOF Director ///////////////////////////////

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\CourseTitleList'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_coursetitlelisttype';
    }
}
