<?php

// autoloader
require_once(__DIR__.'/../vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php');

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Mandango'          => __DIR__.'/../src',
    'Mandango\Tests'    => __DIR__,
    'Mandango\Mondator' => __DIR__.'/../vendor/mondator/src',
    'Doctrine\Common'   => __DIR__.'/../vendor/doctrine-common/lib',
    'Model'             => __DIR__,
));
$loader->register();

// mondator
$configClasses = array(
    'Model\Article' => array(
        'collection' => 'articles',
        'fields' => array(
            'title'        => 'string',
            'content'      => 'string',
            'note'         => 'string',
            'line'         => 'string',
            'text'         => 'string',
            'is_active'    => 'boolean',
            'date'         => 'date',
            'author_id'    => 'reference_one',
            'category_ids' => 'reference_many',
        ),
        'embeddeds_one' => array(
            'source' => array('class' => 'Model\Source'),
        ),
        'embeddeds_many' => array(
            'comments' => array('class' => 'Model\Comment'),
        ),
        'references_one' => array(
            'author' => array('class' => 'Model\Author', 'field' => 'author_id'),
        ),
        'references_many' => array(
            'categories' => array('class' => 'Model\Category', 'field' => 'category_ids'),
        ),
        'indexes' => array(
            array(
                'keys'    => array('slug' => 1),
                'options' => array('unique' => true),
            ),
            array(
                'keys' => array('author_id' => 1, 'is_active' => 1),
            ),
        ),
    ),
    'Model\Author' => array(
        'fields' => array(
            'name' => 'string',
        ),
    ),
    'Model\Category' => array(
        'fields' => array(
            'name' => 'string',
        ),
    ),
    'Model\Comment' => array(
        'is_embedded' => true,
        'fields' => array(
            'name' => 'string',
            'text' => 'string',
            'note' => 'string',
            'line' => 'string',
            'author_id'    => 'reference_one',
            'category_ids' => 'reference_many',
        ),
        'references_one' => array(
            'author' => array('class' => 'Model\Author', 'field' => 'author_id'),
        ),
        'references_many' => array(
            'categories' => array('class' => 'Model\Category', 'field' => 'category_ids'),
        ),
        'embeddeds_many' => array(
            'infos' => array('class' => 'Model\Info'),
        ),
        'indexes' => array(
            array(
                'keys'    => array('line' => 1),
                'options' => array('unique' => true),
            ),
            array(
                'keys' => array('author_id' => 1, 'note' => 1),
            ),
        ),
    ),
    'Model\Info' => array(
        'is_embedded' => true,
        'fields' => array(
            'name' => 'string',
            'text' => 'string',
            'note' => 'string',
            'line' => 'string',
        ),
        'indexes' => array(
            array(
                'keys'    => array('note' => 1),
                'options' => array('unique' => true),
            ),
            array(
                'keys' => array('name' => 1, 'line' => 1),
            ),
        ),
    ),
    'Model\Source' => array(
        'is_embedded' => true,
        'fields' => array(
            'name' => 'string',
            'text' => 'string',
            'note' => 'string',
            'line' => 'string',
            'author_id'    => 'reference_one',
            'category_ids' => 'reference_many',
        ),
        'references_one' => array(
            'author' => array('class' => 'Model\Author', 'field' => 'author_id'),
        ),
        'references_many' => array(
            'categories' => array('class' => 'Model\Category', 'field' => 'category_ids'),
        ),
        'embeddeds_one' => array(
            'info' => array('class' => 'Model\Info'),
        ),
        'indexes' => array(
            array(
                'keys'    => array('name' => 1),
                'options' => array('unique' => true),
            ),
            array(
                'keys' => array('author_id' => 1, 'line' => 1),
            ),
        ),
    ),
    // reference to same class
    'Model\Message' => array(
        'fields' => array(
            'author'      => 'string',
            'reply_to_id' => 'reference_one',
        ),
        'references_one' => array(
            'reply_to' => array('class' => 'Model\Message', 'field' => 'reply_to_id'),
        ),
    ),
    // default values
    'Model\Book' => array(
        'fields' => array(
            'title'   => 'string',
            'comment' => array('type' => 'string', 'default' => 'good'),
            'is_here' => array('type' => 'boolean', 'default' => true),
        ),
    ),
    // events
    'Model\Events' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'events' => array(
            'preInsert'  => array('myPreInsert'),
            'postInsert' => array('myPostInsert'),
            'preUpdate'  => array('myPreUpdate'),
            'postUpdate' => array('myPostUpdate'),
            'preDelete'  => array('myPreDelete'),
            'postDelete' => array('myPostDelete'),
        ),
    ),
    'Model\EventsEmbeddedOne' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'embeddeds_one' => array(
            'embedded' => array('class' => 'Model\EmbeddedEvents'),
        ),
    ),
    'Model\EventsEmbeddedMany' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'embeddeds_many' => array(
            'embedded' => array('class' => 'Model\EmbeddedEvents'),
        ),
    ),
    'Model\EmbeddedEvents' => array(
        'is_embedded' => true,
        'fields' => array(
            'name' => 'string',
        ),
        'events' => array(
            'preInsert'  => array('myPreInsert'),
            'postInsert' => array('myPostInsert'),
            'preUpdate'  => array('myPreUpdate'),
            'postUpdate' => array('myPostUpdate'),
            'preDelete'  => array('myPreDelete'),
            'postDelete' => array('myPostDelete'),
        ),
    ),
    // gridfs
    'Model\Image' => array(
        'is_file' => true,
        'fields' => array(
            'name' => 'string',
        ),
    ),
    // global connection
    'Model\ConnectionGlobal' => array(
        'connection' => 'global',
        'fields' => array(
            'field' => 'string',
        ),
    ),
);

use Mandango\Mondator\Mondator;

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Mandango\Extension\Core(array(
        'metadata_class'  => 'Model\Mapping\Metadata',
        'metadata_output' => __DIR__.'/Model/Mapping',
        'default_output'  => __DIR__.'/Model',
    )),
    new Mandango\Extension\DocumentArrayAccess(),
    new Mandango\Extension\DocumentPropertyOverloading(),
));
$mondator->process();
