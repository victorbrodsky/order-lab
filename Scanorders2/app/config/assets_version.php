<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 7/7/14
 * Time: 1:28 PM
 */

//Here used: http://blog.lavoie.sl/2012/10/automatic-cache-busting-using-git-in-symfony2.html
//The idea is to use the current Git commit as a version number using git rev-parse --short HEAD:
// 'assets_version' => exec('git rev-parse --short HEAD'),
//Use timestamp for now

$container->loadFromExtension('framework', array(
    'templating' => array(
        'engines' => array('twig'),
        //'assets_version' => time(),    //exec('git rev-parse --short HEAD'),
    ),
    'assets' => array(
        'version' => time()
    )
));


//Another solution: http://www.lampjunkie.com/2008/05/append-revision-number-to-css-and-js-includes-in-symfony/
//override the AssetHelper.php file that is within the symfony core files. All you have to do is copy this file from {SYMFONY_LIB_DIR}/helper/ to {YOUR_PROJECT_DIR}/lib/helper/AssetHelper.php.
