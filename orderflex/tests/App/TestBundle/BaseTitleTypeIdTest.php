<?php

namespace Tests\App\TestBundle;

use App\UserdirectoryBundle\Entity\AdministrativeTitle;
use App\UserdirectoryBundle\Entity\AppointmentTitle;
use App\UserdirectoryBundle\Entity\Institution;
use App\UserdirectoryBundle\Entity\MedicalTitle;
use App\UserdirectoryBundle\Form\AdministrativeTitleType;
use App\UserdirectoryBundle\Form\AppointmentTitleType;
use App\UserdirectoryBundle\Form\MedicalTitleType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Regression test for the hidden 'id' field of BaseTitleType (Administrative/Appointment/Medical titles).
 *
 * The field used to be mapped: a submitted empty id (e.g. activation page that did not render it)
 * called setId(null) on a persisted title and the flush failed with
 * "null value in column id of relation user_administrativetitle violates not-null constraint".
 * The field is now unmapped and only populated from the entity for display.
 *
 * Run: ./vendor/bin/phpunit tests/App/TestBundle/BaseTitleTypeIdTest.php
 */
class BaseTitleTypeIdTest extends KernelTestCase
{
    private const TITLE_ID = 12345;

    public static function titleTypesProvider(): array
    {
        return array(
            'administrative' => array(AdministrativeTitleType::class, AdministrativeTitle::class, 'Administrative', 'administrativetitletype'),
            'appointment' => array(AppointmentTitleType::class, AppointmentTitle::class, 'Academic Appointment', 'appointmenttitletype'),
            'medical' => array(MedicalTitleType::class, MedicalTitle::class, 'Medical Appointment', 'medicaltitletype'),
        );
    }

    /**
     * @dataProvider titleTypesProvider
     */
    public function testEmptySubmittedIdDoesNotNullPersistedTitleId($formTypeClass, $titleClass, $label, $formname)
    {
        $title = $this->createPersistedTitle($titleClass);
        $form = $this->createTitleForm($formTypeClass, $title, $titleClass, $label, $formname);

        //same as a browser posting an empty hidden input
        $form->submit(array('id' => ''), false);

        $this->assertTrue($form->isSubmitted());
        $this->assertSame(self::TITLE_ID, $title->getId(), 'Empty submitted id must not reset the title id');
    }

    /**
     * @dataProvider titleTypesProvider
     */
    public function testTamperedSubmittedIdDoesNotChangePersistedTitleId($formTypeClass, $titleClass, $label, $formname)
    {
        $title = $this->createPersistedTitle($titleClass);
        $form = $this->createTitleForm($formTypeClass, $title, $titleClass, $label, $formname);

        $form->submit(array('id' => '999'), false);

        $this->assertSame(self::TITLE_ID, $title->getId(), 'Submitted id must not be written to the title');
    }

    /**
     * @dataProvider titleTypesProvider
     */
    public function testHiddenIdIsRenderedFromEntity($formTypeClass, $titleClass, $label, $formname)
    {
        $title = $this->createPersistedTitle($titleClass);
        $form = $this->createTitleForm($formTypeClass, $title, $titleClass, $label, $formname);

        $this->assertFalse($form->get('id')->getConfig()->getMapped(), "'id' field must not be mapped");
        //unmapped field is populated by POST_SET_DATA, so the hidden input still renders the real id
        $this->assertEquals(self::TITLE_ID, $form->get('id')->getData());
    }

    /**
     * @dataProvider titleTypesProvider
     */
    public function testNewTitleHasEmptyHiddenId($formTypeClass, $titleClass, $label, $formname)
    {
        $title = new $titleClass();
        $title->setInstitution(new Institution());
        $form = $this->createTitleForm($formTypeClass, $title, $titleClass, $label, $formname);

        $this->assertNull($form->get('id')->getData());
        $this->assertNull($title->getId());
    }

    private function createPersistedTitle($titleClass)
    {
        $title = new $titleClass();
        $title->setId(self::TITLE_ID);
        //an institution skips the default-institution lookup (container/DB) in BaseTitleType PRE_SET_DATA
        $title->setInstitution(new Institution());
        return $title;
    }

    private function createTitleForm($formTypeClass, $title, $titleClass, $label, $formname)
    {
        self::bootKernel();
        $formFactory = static::getContainer()->get('form.factory');

        //stub em: BaseTitleType only asks the Institution repository for tree level labels
        $em = new class {
            public function getRepository($className)
            {
                return new class {
                    public function getLevelLabels($institution) { return 'Institution'; }
                };
            }
        };

        //'container' is omitted on purpose: BaseTitleType then skips the logged in user role check
        $params = array(
            'em' => $em,
            'cycle' => 'edit',
            'disabled' => false,
            'label' => $label,
            'fullClassName' => $titleClass,
            'formname' => $formname,
        );

        return $formFactory->create($formTypeClass, $title, array(
            'form_custom_value' => $params,
            'csrf_protection' => false,
        ));
    }
}
