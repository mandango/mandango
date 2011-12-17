<?php

$vendorDir = __DIR__.'/../vendor';
require_once $vendorDir.'/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Mandango'          => __DIR__.'/../src',
    'Mandango\Tests'    => __DIR__,
    'Mandango\Mondator' => $vendorDir.'/mondator/src',
    'Model'             => __DIR__,
));
$loader->registerPrefixes(array(
    'Twig_' => $vendorDir.'/twig/lib',
));
$loader->register();

// mondator
$configClasses = require __DIR__.'/config_classes.php';

use Mandango\Mondator\Mondator;

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Mandango\Extension\Core(array(
        'metadata_factory_class'  => 'Model\Mapping\Metadata',
        'metadata_factory_output' => __DIR__.'/Model/Mapping',
        'default_output'          => __DIR__.'/Model',
    )),
    new Mandango\Extension\DocumentArrayAccess(),
    new Mandango\Extension\DocumentPropertyOverloading(),
));
$mondator->process();
