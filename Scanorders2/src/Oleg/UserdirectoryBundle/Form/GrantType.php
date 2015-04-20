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

    private $commentData = null;
    private $effortData = null;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //echo "cycle=".$this->params['cycle']."<br>";

        if( strpos($this->params['cycle'],'_standalone') === false ) {
            $readonly = true;
        } else {
            $readonly = false;
        }

        $builder->add('id','hidden',array(
            'label'=>false,
            'attr' => array('class'=>'grant-id-field')
        ));

        $builder->add('grantid',null,array(
            'read_only' => $readonly,
            'label'=>'Grant ID Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('amount',null,array(
            'read_only' => $readonly,
            'label'=>'Total Amount:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('currentYearDirectCost',null,array(
            'read_only' => $readonly,
            'label'=>'Current Year Direct Cost:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('currentYearIndirectCost',null,array(
            'read_only' => $readonly,
            'label'=>'Current Year Indirect Cost:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('totalCurrentYearCost',null,array(
            'read_only' => $readonly,
            'label'=>'Total Current Year Cost:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('amountLabSpace',null,array(
            'read_only' => $readonly,
            'label'=>'Amount of Lab Space:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('startDate', 'date', array(
            'read_only' => $readonly,
            'label' => "Grant Support Start Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

        $builder->add('endDate', 'date', array(
            'read_only' => $readonly,
            'label' => "Grant Support End Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

        $builder->add('sourceOrganization', 'employees_custom_selector', array(
            'read_only' => $readonly,
            'label' => "Source Sponsor Organization:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-sourceorganization', 'type' => 'hidden'),
            'classtype' => 'sourceorganization'
        ));

        $builder->add('grantLink', 'employees_custom_selector', array(
            'read_only' => $readonly,
            'label' => "Link to a page with more information:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-grantlink', 'type' => 'hidden'),
            'classtype' => 'grantlink'
        ));


        //Relevant Documents
        $params = array('labelPrefix'=>'Relevant Document');
        $params['document.showall'] = false;
        $params['read_only'] = $readonly;
        $builder->add('attachmentContainer', new AttachmentContainerType($params), array(
            'required' => false,
            'label' => false
        ));


        //Consider stanAlone for all cycles with _standalone, except new_standalone. Cycle new_standalone is exception because we don't show list attributes in creation page
        if( strpos($this->params['cycle'],'_standalone') !== false && strpos($this->params['cycle'],'new') === false ) {
            //list attributes
            $params = array();
            $mapper = array();
            $params['user'] = $this->params['user'];
            $params['cycle'] = $this->params['cycle'];
            $params['standalone'] = true;
            $mapper['className'] = "Grant";
            $mapper['bundleName'] = "OlegUserdirectoryBundle";

            $builder->add('list', new ListType($params, $mapper), array(
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\Grant',
                'label' => false
            ));
        }




//        $builder->add('effort', 'employees_custom_selector', array(
//            'label' => 'Percent Effort:',
//            'attr' => array('class' => 'ajax-combobox-effort', 'type' => 'hidden', "data-inputmask"=>"'mask': '[o]', 'repeat': 10, 'greedy' : false"),
//            'required' => false,
//            'classtype' => 'effort'
//        ));
//
//        $builder->add('comment', 'textarea', array(
//            'label'=>'Comment:',
//            'required'=>false,
//            'attr' => array('class' => 'textarea form-control')
//        ));




        if( strpos($this->params['cycle'],'_standalone') === false ) {

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

                $grant = $event->getData();
                $form = $event->getForm();

                $form->add('grantTitle', 'employees_custom_selector', array(
                    'read_only' => ($grant && $grant->getId() ? true : false),
                    'label' => "Grant Title:",
                    'required' => false,
                    'attr' => array('class' => 'combobox combobox-width ajax-combobox-granttitle', 'type' => 'hidden'),
                    'classtype' => 'granttitle'
                ));

                if( $grant && $grant->getId() && $this->params['subjectUser'] ) {

                    $comment = $this->params['em']->getRepository('OlegUserdirectoryBundle:GrantComment')->findOneBy(
                        array(
                            'grant' => $grant,
                            'author' => $this->params['subjectUser']
                        )
                    );

                    if( $comment ) {
                        $this->commentData = $comment->getComment();
                    }

                    $effort = $this->params['em']->getRepository('OlegUserdirectoryBundle:GrantEffort')->findOneBy(
                        array(
                            'grant' => $grant,
                            'author' => $this->params['subjectUser']
                        )
                    );

                    if( $effort ) {
                        $this->effortData = $effort;
                    }

                }


            });


            $builder->add('commentDummy','textarea',array(
                'mapped' => false,
                'data' => $this->commentData,
                'required' => false,
                'label'=>'Comment:',
                'attr' => array('class'=>'textarea form-control grant-commentDummy-field')
            ));

            $builder->add('effortDummy', 'employees_custom_selector', array(
                'mapped' => false,
                'data' => $this->effortData,
                'required' => false,
                'label' => 'Percent Effort:',
                'attr' => array('class'=>'ajax-combobox-effort grant-effortDummy-field', "data-inputmask"=>"'mask': '[o]', 'repeat': 10, 'greedy' : false"),
                'classtype' => 'effort'
            ));

        } else {

            $builder->add('grantTitle', 'employees_custom_selector', array(
                'label'=>"Grant Title:",
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-granttitle', 'type' => 'hidden'),
                'classtype' => 'granttitle'
            ));

        }


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
