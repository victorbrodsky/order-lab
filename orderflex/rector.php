<?php

use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SensiolabsSetList;
//use Rector\Nette\Set\NetteSetList;
use Rector\Config\RectorConfig;

//https://getrector.com/blog/how-to-upgrade-annotations-to-attributes
// vendor/bin/rector process src/App/DeidentifierBundle/Entity/
// vendor/bin/rector process src/App/DeidentifierBundle/Controller --dry-run

return function (RectorConfig $rectorConfig): void {

    //$rectorConfig->disableParallel();

    $rectorConfig->sets([
        //SymfonySetList::SYMFONY_62,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES,
        //SensiolabsSetList::FRAMEWORK_EXTRA_63,
        //NetteSetList::ANNOTATIONS_TO_ATTRIBUTES,
        //SensiolabsSetList::FRAMEWORK_EXTRA_61,
        //SymfonyLevelSetList::UP_TO_SYMFONY_63
    ]);
};

