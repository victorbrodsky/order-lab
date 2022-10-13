<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\UserdirectoryBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class BackupManagementType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);
        
        $builder->add('dbBackupConfig', TextareaType::class, array(
            'label'=>'Configuration json file for backup DB (dbBackup cron job):',
            'required'=>false,
            'attr' => array('class'=>'form-control textarea backup_management_dbBackupConfig', ),
        ));

        $builder->add('filesBackupConfig', TextareaType::class, array(
            'label'=>'Configuration json file for backup uploaded folder (filesBackup cron job):',
            'required'=>false,
            'attr' => array('class'=>'form-control textarea backup_management_filesBackupConfig' ),
        ));

        if( $this->params['cycle'] == "edit") {
            $builder->add('submit', SubmitType::class, array(
                'label' => 'Update',
                'attr' => array('class' => 'btn btn-primary'),
            ));
        }
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\SiteParameters',
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'siteparameters_backup_management';
    }
}
