<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 8/8/2023
 * Time: 5:09 PM
 */

//Get current GIT version
//https://stackoverflow.com/questions/16334310/display-the-current-git-version-in-php

namespace App\UserdirectoryBundle\Util;


class ApplicationVersion
{
    const MAJOR = 1;
    const MINOR = 2;
    const PATCH = 3;

    public static function get()
    {
        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));

        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));

        return sprintf('v%s.%s.%s-dev.%s (%s)', self::MAJOR, self::MINOR, self::PATCH, $commitHash, $commitDate->format('Y-m-d H:i:s'));
    }
}