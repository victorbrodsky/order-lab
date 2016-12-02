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

    //private $commentData = null;
    //private $effortData = null;

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
            $standalone = false;
        } else {
            $readonly = false;
            $standalone = true;
        }

        $builder->add('id','hidden',array(
            'label'=>false,
            'attr' => array('class'=>'grant-id-field')
        ));

        $builder->add('grantid',null,array(
            'read_only' => $readonly,
            'label'=>'Grant ID Number:',
            'attr' => array('class'=>'form-control grant-grantid-field')
        ));

        $builder->add('amount',null,array(
            'read_only' => $readonly,
            'label'=>'Total Amount:',
            'attr' => array('class'=>'form-control grant-amount-field')
        ));

        $builder->add('currentYearDirectCost',null,array(
            'read_only' => $readonly,
            'label'=>'Current Year Direct Cost:',
            'attr' => array('class'=>'form-control grant-currentYearDirectCost-field')
        ));

        $builder->add('currentYearIndirectCost',null,array(
            'read_only' => $readonly,
            'label'=>'Current Year Indirect Cost:',
            'attr' => array('class'=>'form-control grant-currentYearIndirectCost-field')
        ));

        $builder->add('totalCurrentYearCost',null,array(
            'read_only' => $readonly,
            'label'=>'Total Current Year Cost:',
            'attr' => array('class'=>'form-control grant-totalCurrentYearCost-field')
        ));

        $builder->add('amountLabSpace',null,array(
            'read_only' => $readonly,
            'label'=>'Amount of Lab Space:',
            'attr' => array('class'=>'form-control grant-amountLabSpace-field')
        ));

        $builder->add('startDate', 'date', array(
            'read_only' => $readonly,
            'label' => "Grant Support Start Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',    //'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control grant-startDate-field'),
        ));

        $builder->add('endDate', 'date', array(
            'read_only' => $readonly,
            'label' => "Grant Support End Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control grant-endDate-field'),
        ));

        $builder->add('sourceOrganization', 'employees_custom_selector', array(
            'read_only' => $readonly,
            'label' => "Grant Source Organization (Sponsor):",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-sourceorganization', 'type' => 'hidden'),
            'classtype' => 'sourceorganization'
        ));

        $builder->add('grantLink', null, array(
            'read_only' => $readonly,
            'label' => 'Link to a page with more information:',
            'attr' => array('class'=>'form-control grant-grantLink-field')
        ));


        //Relevant Documents
        $params = array('labelPrefix'=>'Relevant Document');
        $params['document.showall'] = false;
        $params['document.imageId'] = false;
        $params['document.source'] = false;
        //$params['read_only'] = $readonly;
        $builder->add('attachmentContainer', new AttachmentContainerType($params), array(
            'required' => false,
            'label' => false
        ));


        //Consider stanAlone for all cycles with _standalone, except new_standalone. Cycle new_standalone is exception because we don't show list attributes in creation page
        //if( strpos($this->params['cycle'],'_standalone') !== false && strpos($this->params['cycle'],'new') === false ) {
        if( $standalone ) {
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



        if( !$standalone ) {

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

                $grant = $event->getData();
                $form = $event->getForm();

                $form->add('name', 'employees_custom_selector', array(
                    'read_only' => ($grant && $grant->getId() ? true : false),
                    'label' => "Grant Title:",
                    'required' => false,
                    'attr' => array('class' => 'combobox combobox-width ajax-combobox-grant', 'type' => 'hidden'),
                    'classtype' => 'grant'
                ));

                if( $grant && $grant->getId() && $this->params['subjectUser'] ) {

                    //comment
                    $comment = $this->params['em']->getRepository('OlegUserdirectoryBundle:GrantComment')->findOneBy(
                        array(
                            'grant' => $grant,
                            'author' => $this->params['subjectUser']
                        )
                    );

                    //exit("grant=".$grant->getId().", user=".$this->params['subjectUser']->getId()." => comment=".$comment);
                    if( $comment ) {
                        $grant->setCommentDummy($comment->getComment());
                    }

                    //effort
                    $effort = $this->params['em']->getRepository('OlegUserdirectoryBundle:GrantEffort')->findOneBy(
                        array(
                            'grant' => $grant,
                            'author' => $this->params['subjectUser']
                        )
                    );

                    if( $effort ) {
                        $grant->setEffortDummy($effort->getEffort());
                    }

                }


            });


            //exit('this->commentData='.$this->commentData);

            $builder->add('commentDummy','textarea',array(
                //'mapped' => false,
                'required' => false,
                'label'=>'Comment:',
                'attr' => array('class'=>'textarea form-control grant-commentDummy-field')
            ));

            $builder->add('effortDummy', 'employees_custom_selector', array(
                //'mapped' => false,
                'required' => false,
                'label' => 'Percent Effort:',
                'attr' => array('class'=>'ajax-combobox-effort grant-effortDummy-field'),
                //'attr' => array('class' => 'ajax-combobox-effort grant-effortDummy-field', 'type' => 'hidden', "data-inputmask"=>"'mask': '[o]', 'repeat': 10, 'greedy' : false"),
                'classtype' => 'effort'
            ));

        } else {

            $builder->add('name',null,array(
                'label'=>"Grant Title:",
                'required' => true,
                'attr' => array('class' => 'form-control')
            ));

        }


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Grant',
            //'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_grant';
    }
}
