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

namespace App\DemoDbBundle\Util;



use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Panther\Client;


/**
 * @author oli2002
 */
class DemoDbUtil {

    protected $em;
    protected $container;
    //private $baseUrl = 'https://view.online/c/demo-institution/demo-department';
    private $baseUrl = 'http://127.0.0.1';

    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container
    )
    {
        $this->em = $em;
        $this->container = $container;
    }


    public function getClient() {
        $client = Client::createChromeClient(
            $this->container->get('kernel')->getProjectDir().'/drivers/chromedriver',[
                '--remote-debugging-port=9222',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--headless'
            ]
        );
        //$client = self::createPantherClient();
        return $client;
    }

    public function loginAction() {
        $client = $this->getClient();

        $url = $this->baseUrl.'/directory/login';
        $url = 'http://127.0.0.1:8000/directory/directory/login';

        $crawler = $client->refreshCrawler();
        $crawler = $client->request('GET', $url);

        $form = $crawler->selectButton('Log In')->form();

        //Select an option in select2 combobox:
        //Element is not currently visible and may not be manipulated
        //Webscrapper: how select2
        //https://symfony.com/doc/current/components/dom_crawler.html
        $crawler = $client->waitForVisibility('#s2id_usernametypeid_show');
        //$client->waitFor('_usernametype');

        //$myInput = $crawler->filterXPath(".//select[@id='usernametypeid_show']//option[@value='local-user']");
        //$myInput = $crawler->filter('#s2id_usernametypeid_show');
        //$myInput = $crawler->filterXPath(".//select[@id='s2id_usernametypeid_show']//option[@value='local-user']");
        //$myInput = $crawler->filterXPath(".//select[@id='usernametypeid_show']//option[@value='local-user']");
        //$form['_usernametype']->setValues(array('local-user'));
        //$form['registration[birthday][year]']->select(1984);
//        'select2-result-label-17'
        //$client->waitFor('_usernametype');

        //$myInput = $crawler->filterXPath(".//select[@id='s2id_usernametypeid_show']//option[@value='local-user']");
        //$myInput = $crawler->filterXPath(".//div[@id='s2id_usernametypeid_show']//option[@value='local-user']");
        //$myInput->click();
        //$myInput = $crawler->filterXPath(".//div[@id='s2id_usernametypeid_show']");
        //$myInput = $crawler->filterXPath(".//select[@id='s2id_usernametypeid_show']//option[@value='local-user']");

        //Working: executed JS script to click on a select2 and choose select2 element
        $client->executeScript("$('#s2id_usernametypeid_show').select2('val','local-user')");

        //wait for new element on new page to appear
        //$client->waitForVisibility('select2-dropdown-open');
        //$client->waitForVisibility('Local User');


        //$form['#usernametypeid_show'] = 'local-user';
        //$client->waitForVisibility('#select2-chosen-1');
        //$myInput->click(); //error: Element is not currently visible and may not be manipulated

        $form['_display-username'] = 'administrator';
        $form['_password'] = 'demo';

        $client->submit($form);

        return $client;
    }

}


?>
