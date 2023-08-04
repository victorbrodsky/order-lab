<?php

use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\SensiolabsSetList;
//use Rector\Nette\Set\NetteSetList;
use Rector\Config\RectorConfig;

//https://getrector.com/blog/how-to-upgrade-annotations-to-attributes
// vendor/bin/rector process src/App/DeidentifierBundle/Entity/
// vendor/bin/rector process src/App/DeidentifierBundle/Controller

return function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        //SymfonySetList::SYMFONY_62,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        //NetteSetList::ANNOTATIONS_TO_ATTRIBUTES,
        //SensiolabsSetList::FRAMEWORK_EXTRA_61,
        //SymfonyLevelSetList::UP_TO_SYMFONY_63
    ]);
};

