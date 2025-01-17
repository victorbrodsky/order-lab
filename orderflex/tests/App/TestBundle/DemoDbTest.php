<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 1/16/2025
 * Time: 5:36 PM
 */

namespace Tests\App\TestBundle;

use Tests\App\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DemoDbTest extends WebTestBase
{

    public function testCreateUser() {

        $this->logIn();

        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/user/new');

        //$primaryPublicUserId = $crawler->filter('#oleg_userdirectorybundle_user_primaryPublicUserId');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $form = $crawler->selectButton('btnSubmit')->form();

        $form['oleg_userdirectorybundle_user[keytype]']->select(3);

        $form['oleg_userdirectorybundle_user[primaryPublicUserId]']->setValue('johndoe1');

        $this->client->submit($form);

        //$this->assertContains(
        //    'Preferred Contact Info:',
        //    $this->client->getResponse()->getContent()
        //);

        //$btns = $crawler->filter('#btn-send-latest-invoice-pdf-email');
        //$btnsCount = $btns->count();

        //$crawler = $this->client->click($link);

    }

}