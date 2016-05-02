<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SiteParametersType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        $always_empty = true;
//
//        if( $this->params['cycle'] != 'show' ) {
//            $always_empty = false;
//        }

        //echo "$always_empty=".$always_empty."<br>";

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'maxIdleTime' )
        $builder->add('maxIdleTime',null,array(
            'label'=>'Max Idle Time (min):',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'environment' )
        $builder->add('environment','choice',array(
            'label'=>'Environment:',
            'choices' => array("live"=>"live", "dev"=>"dev"),
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'siteEmail' )
        $builder->add('siteEmail','email',array(
            'label'=>'Site Email:',
            'attr' => array('class'=>'form-control')
        ));

        //smtp
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'smtpServerAddress' )
        $builder->add('smtpServerAddress',null,array(
            'label'=>'SMTP Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        //scan order DB
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbServerAddress' )
        $builder->add('dbServerAddress',null,array(
            'label'=>'ScanOrder DB Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbServerPort' )
        $builder->add('dbServerPort',null,array(
            'label'=>'ScanOrder DB Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbServerAccountUserName' )
        $builder->add('dbServerAccountUserName',null,array(
            'label'=>'ScanOrder DB Server Account User Name:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbServerAccountPassword' )
        $builder->add('dbServerAccountPassword',null,array(
            'label'=>'ScanOrder DB Server Account Password:',
            //'always_empty' => $always_empty,
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbDatabaseName' )
        $builder->add('dbDatabaseName',null,array(
            'label'=>'ScanOrder Database Name:',
            'attr' => array('class'=>'form-control')
        ));

        //LDAP
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerAddress' )
        $builder->add('aDLDAPServerAddress',null,array(
            'label'=>'AD/LDAP Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerPort' )
        $builder->add('aDLDAPServerPort',null,array(
            'label'=>'AD/LDAP Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerOu' )
        $builder->add('aDLDAPServerOu',null,array(
            'label'=>'AD/LDAP Server OU (DCs):',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerAccountUserName' )
        $builder->add('aDLDAPServerAccountUserName',null,array(
            'label'=>'AD/LDAP Server Account User Name:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerAccountPassword' )
        $builder->add('aDLDAPServerAccountPassword',null,array(
            'label'=>'AD/LDAP Server Account Password:',
            //'always_empty' => $always_empty,
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapExePath' )
            $builder->add('ldapExePath',null,array(
                'label'=>'LDAP/AD Authenticator Path (Default: "../src/Oleg/UserdirectoryBundle/Util/" ):',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapExeFilename' )
            $builder->add('ldapExeFilename',null,array(
                'label'=>'LDAP/AD Authenticator File Name (Default: "LdapSaslCustom.exe" ):',
                'attr' => array('class'=>'form-control')
            ));


        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'autoAssignInstitution' )
            $builder->add('autoAssignInstitution',null,array(
                'label'=>'Auto-Assign Institution name:',
                'attr' => array('class'=>'form-control')
            ));




        //Aperio DB
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aperioeSlideManagerDBServerAddress' )
        $builder->add('aperioeSlideManagerDBServerAddress',null,array(
            'label'=>'Aperio eSlide Manager DB Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aperioeSlideManagerDBServerPort' )
        $builder->add('aperioeSlideManagerDBServerPort',null,array(
            'label'=>'Aperio eSlide Manager DB Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aperioeSlideManagerDBUserName' )
        $builder->add('aperioeSlideManagerDBUserName',null,array(
            'label'=>'Aperio eSlide Manager DB Server User Name:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aperioeSlideManagerDBPassword' )
        $builder->add('aperioeSlideManagerDBPassword',null,array(
            'label'=>'Aperio eSlide Manager DB Server Password:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aperioeSlideManagerDBName' )
        $builder->add('aperioeSlideManagerDBName',null,array(
            'label'=>'Aperio eSlide Manager Database Name:',
            'attr' => array('class'=>'form-control')
        ));

        //footer
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'institutionurl' )
            $builder->add('institutionurl',null,array(
                'label'=>'Institution URL:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'institutionname' )
            $builder->add('institutionname',null,array(
                'label'=>'Institution Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'departmenturl' )
            $builder->add('departmenturl',null,array(
                'label'=>'Department URL:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'departmentname' )
            $builder->add('departmentname',null,array(
                'label'=>'Department Name:',
                'attr' => array('class'=>'form-control')
            ));

        //maintenance
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'maintenance' )
            $builder->add('maintenance',null,array(
                'label'=>'Maintenance:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'maintenanceenddate' )
            $builder->add('maintenanceenddate',null,array(
                'label'=>'Maintenance Until:',
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy H:m',
                'attr' => array('class'=>'form-control datetimepicker')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'maintenanceloginmsg' )
            $builder->add('maintenanceloginmsg',null,array(
                'label'=>'Maintenance Login Message:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'maintenancelogoutmsg' )
            $builder->add('maintenancelogoutmsg',null,array(
                'label'=>'Maintenance Logout Message:',
                'attr' => array('class'=>'form-control')
            ));


        //uploads
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'employeesuploadpath' )
            $builder->add('employeesuploadpath',null,array(
                'label'=>'Employee Directory Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'scanuploadpath' )
            $builder->add('scanuploadpath',null,array(
                'label'=>'Scan Orders Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'avataruploadpath' )
            $builder->add('avataruploadpath',null,array(
                'label'=>'Employee Directory Avatar Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'fellappuploadpath' )
            $builder->add('fellappuploadpath',null,array(
                'label'=>'Fellowship Application Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        //vacrequploadpath
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'vacrequploadpath' )
            $builder->add('vacrequploadpath',null,array(
                'label'=>'Vacation Request Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));


        //titles and messages
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mainHomeTitle' )
            $builder->add('mainHomeTitle',null,array(
                'label'=>'Main Home Title:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'listManagerTitle' )
            $builder->add('listManagerTitle',null,array(
                'label'=>'List Manager Title:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'eventLogTitle' )
            $builder->add('eventLogTitle',null,array(
                'label'=>'Event Log Title:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'siteSettingsTitle' )
            $builder->add('siteSettingsTitle',null,array(
                'label'=>'Site Settings Title:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'contentAboutPage' )
            $builder->add('contentAboutPage',null,array(
                'label'=>'About Page Content:',
                'attr' => array('class'=>'form-control')
            ));

        //Fellowship Application parameters
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'codeGoogleFormFellApp' )
            $builder->add('codeGoogleFormFellApp',null,array(
                'label'=>'Path to the local copy of the fellowship application form Code.gs file (https://script.google.com/a/macros/pathologysystems.org/d/14jgVkEBCAFrwuW5Zqiq8jsw37rc4JieHkKrkYz1jyBp_DFFyTjRGKgHj/edit):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'allowPopulateFellApp' )
            $builder->add('allowPopulateFellApp',null,array(
                'label'=>'Periodically import fellowship applications submitted via the Google form:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'confirmationSubjectFellApp' )
            $builder->add('confirmationSubjectFellApp',null,array(
                'label'=>'Email subject for confirmation of application submission:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'confirmationBodyFellApp' )
            $builder->add('confirmationBodyFellApp',null,array(
                'label'=>'Email body for confirmation of application submission:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'confirmationEmailFellApp' )
            $builder->add('confirmationEmailFellApp',null,array(
                'label'=>'Email address for confirmation of application submission:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'clientEmailFellApp' )
            $builder->add('clientEmailFellApp',null,array(
                'label'=>'Client Email for accessing the Google Drive API (1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'p12KeyPathFellApp' )
            $builder->add('p12KeyPathFellApp',null,array(
                'label'=>'Full Path to p12 key file for accessing the Google Drive API (E:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\src\Oleg\FellAppBundle\Util\FellowshipApplication-f1d9f98353e5.p12):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'googleDriveApiUrlFellApp' )
            $builder->add('googleDriveApiUrlFellApp',null,array(
                'label'=>'Google Drive API URL (https://www.googleapis.com/auth/drive https://spreadsheets.google.com/feeds):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'userImpersonateEmailFellApp' )
            $builder->add('userImpersonateEmailFellApp',null,array(
                'label'=>'Impersonate the following user email address for accessing the Google Drive API (olegivanov@pathologysystems.org):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'templateIdFellApp' )
            $builder->add('templateIdFellApp',null,array(
                'label'=>'Template Google Spreadsheet ID (1ITacytsUV2yChbfOSVjuBoW4aObSr_xBfpt6m_vab48):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'backupFileIdFellApp' )
            $builder->add('backupFileIdFellApp',null,array(
                'label'=>'Backup Google Spreadsheet ID (19KlO1oCC88M436JzCa89xGO08MJ1txQNgLeJI0BpNGo):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'folderIdFellApp' )
            $builder->add('folderIdFellApp',null,array(
                'label'=>'Application Google Drive Folder ID (0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'backupUpdateDatetimeFellApp' )
//            $builder->add('backupUpdateDatetimeFellApp',null,array(
//                'label'=>'Backup Sheet Last Modified Date:',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'localInstitutionFellApp' )
            $builder->add('localInstitutionFellApp',null,array(
                'label'=>'Local Organizational Group for imported fellowship applications (Pathology Fellowship Programs (WCMC)):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'deleteImportedAplicationsFellApp' )
            $builder->add('deleteImportedAplicationsFellApp',null,array(
                'label'=>"Delete successfully imported applications from Google Drive:",
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'deleteOldAplicationsFellApp' )
            $builder->add('deleteOldAplicationsFellApp',null,array(
                'label'=>'Delete downloaded spreadsheets with fellowship applications after successful import into the database:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'yearsOldAplicationsFellApp' )
            $builder->add('yearsOldAplicationsFellApp',null,array(
                'label'=>'Number of years to keep downloaded spreadsheets with fellowship applications as backup:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'spreadsheetsPathFellApp' )
            $builder->add('spreadsheetsPathFellApp',null,array(
                'label'=>'Path to the downloaded spreadsheets with fellowship applications (fellapp/Spreadsheets):',
                'attr' => array(
                    'class'=>'form-control form-control-modif',
                    'style'=>'margin:0',
                )
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'applicantsUploadPathFellApp' )
            $builder->add('applicantsUploadPathFellApp',null,array(
                'label'=>'Path to the downloaded attached documents (fellapp/FellowshipApplicantUploads):',
                'attr' => array(
                    'class'=>'form-control form-control-modif',
                    'style'=>'margin:0',
                )
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'reportsUploadPathFellApp' )
            $builder->add('reportsUploadPathFellApp',null,array(
                'label'=>'Path to the generated fellowship applications in PDF format (fellapp/Reports):',
                'attr' => array(
                    'class'=>'form-control form-control-modif',
                    'style'=>'margin:0',
                )
            ));


        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'applicationPageLinkFellApp' )
            $builder->add('applicationPageLinkFellApp',null,array(
                'label'=>'Link to the Application Page:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'libreOfficeConvertToPDFPathFellApp' )
            $builder->add('libreOfficeConvertToPDFPathFellApp',null,array(
                'label'=>'Path to LibreOffice for converting a file to pdf (C:\Program Files (x86)\LibreOffice 5\program):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'libreOfficeConvertToPDFFilenameFellApp' )
            $builder->add('libreOfficeConvertToPDFFilenameFellApp',null,array(
                'label'=>'LibreOffice executable file (soffice):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'libreOfficeConvertToPDFArgumentsdFellApp' )
            $builder->add('libreOfficeConvertToPDFArgumentsdFellApp',null,array(
                'label'=>'LibreOffice arguments (--headless -convert-to pdf -outdir):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pdftkPathFellApp' )
            $builder->add('pdftkPathFellApp',null,array(
                'label'=>'Path to pdftk for PDF concatenation (E:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pdftkFilenameFellApp' )
            $builder->add('pdftkFilenameFellApp',null,array(
                'label'=>'pdftk executable file (pdftk):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pdftkArgumentsFellApp' )
            $builder->add('pdftkArgumentsFellApp',null,array(
                'label'=>'pdftk arguments (###inputFiles### cat output ###outputFile### dont_ask):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'gsPathFellApp' )
            $builder->add('gsPathFellApp',null,array(
                'label'=>'Path to Ghostscript for stripping PDF password protection (E:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\vendor\olegutil\Ghostscript\bin):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'gsFilenameFellApp' )
            $builder->add('gsFilenameFellApp',null,array(
                'label'=>'Ghostscript executable file (gswin64c.exe):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'gsArgumentsFellApp' )
            $builder->add('gsArgumentsFellApp',null,array(
                'label'=>'Ghostscript arguments (-q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=###outputFile###  -c .setpdfwrite -f ###inputFiles###):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'academicYearStart' )
            $builder->add('academicYearStart',null,array(
                'label'=>'Academic Year Start (July 1st):',
                //'attr' => array('class'=>'datepicker form-control datepicker-day-month')
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'academicYearEnd' )
            $builder->add('academicYearEnd',null,array(
                'label'=>'Academic Year End (June 30th):',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'holidaysUrl' )
            $builder->add('holidaysUrl',null,array(
                'label'=>'Link to list of holidays (http://intranet.med.cornell.edu/hr/):',
                'attr' => array('class'=>'form-control')
            ));

        $this->addCoPath($builder);

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\SiteParameters'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_userdirectorybundle_siteparameters';
    }


    //Co-Path DB
    public function addCoPath($builder) {

        //Production
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBServerAddress' )
            $builder->add('coPathDBServerAddress',null,array(
                'label'=>'CoPath DB Server Address:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBServerPort' )
            $builder->add('coPathDBServerPort',null,array(
                'label'=>'CoPath DB Server Port:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBAccountUserName' )
            $builder->add('coPathDBAccountUserName',null,array(
                'label'=>'CoPath DB Server Account User Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBAccountPassword' )
            $builder->add('coPathDBAccountPassword',null,array(
                'label'=>'CoPath DB Server Account Password:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBName' )
            $builder->add('coPathDBName',null,array(
                'label'=>'CoPath Database Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISName' )
            $builder->add('LISName',null,array(
                'label'=>'LIS Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISVersion' )
            $builder->add('LISVersion',null,array(
                'label'=>'LIS Version:',
                'attr' => array('class'=>'form-control')
            ));


        //Test
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBServerAddressTest' )
            $builder->add('coPathDBServerAddressTest',null,array(
                'label'=>'Test CoPath DB Server Address:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBServerPortTest' )
            $builder->add('coPathDBServerPortTest',null,array(
                'label'=>'Test CoPath DB Server Port:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBAccountUserNameTest' )
            $builder->add('coPathDBAccountUserNameTest',null,array(
                'label'=>'Test CoPath DB Server Account User Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBAccountPasswordTest' )
            $builder->add('coPathDBAccountPasswordTest',null,array(
                'label'=>'Test CoPath DB Server Account Password:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBNameTest' )
            $builder->add('coPathDBNameTest',null,array(
                'label'=>'Test CoPath Database Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISNameTest' )
            $builder->add('LISNameTest',null,array(
                'label'=>'Test LIS Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISVersionTest' )
            $builder->add('LISVersionTest',null,array(
                'label'=>'Test LIS Version:',
                'attr' => array('class'=>'form-control')
            ));


        //Development
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBServerAddressDevelopment' )
            $builder->add('coPathDBServerAddressDevelopment',null,array(
                'label'=>'Development CoPath DB Server Address:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBServerPortDevelopment' )
            $builder->add('coPathDBServerPortDevelopment',null,array(
                'label'=>'Development CoPath DB Server Port:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBAccountUserNameDevelopment' )
            $builder->add('coPathDBAccountUserNameDevelopment',null,array(
                'label'=>'Development CoPath DB Server Account User Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBAccountPasswordDevelopment' )
            $builder->add('coPathDBAccountPasswordDevelopment',null,array(
                'label'=>'Development CoPath DB Server Account Password:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'coPathDBNameDevelopment' )
            $builder->add('coPathDBNameDevelopment',null,array(
                'label'=>'Development CoPath Database Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISNameDevelopment' )
            $builder->add('LISNameDevelopment',null,array(
                'label'=>'Development LIS Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISVersionDevelopment' )
            $builder->add('LISVersionDevelopment',null,array(
                'label'=>'Development LIS Version:',
                'attr' => array('class'=>'form-control')
            ));


    }

}
