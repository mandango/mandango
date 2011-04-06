<?php

// autoloader
require_once(__DIR__.'/../vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php');

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Mandango'          => __DIR__.'/../src',
    'Mandango\Tests'    => __DIR__,
    'Mandango\Mondator' => __DIR__.'/../vendor/mondator/src',
    'Model'             => __DIR__,
));
$loader->register();

// mondator
$configClasses = array(
    'Model\Article' => array(
        'collection' => 'articles',
        'fields' => array(
            'title'     => 'string',
            'content'   => 'string',
            'note'      => 'string',
            'line'      => 'string',
            'text'      => 'string',
            'is_active' => 'boolean',
            'date'      => 'date',
            'database'  => array('alias' => 'basatos', 'type' => 'string'),
        ),
        'embeddeds_one' => array(
            'source' => array('class' => 'Model\Source'),
        ),
        'embeddeds_many' => array(
            'comments' => array('class' => 'Model\Comment'),
        ),
        'references_one' => array(
            'author'      => array('class' => 'Model\Author', 'field' => 'author_id'),
            'information' => array('class' => 'Model\ArticleInformation', 'field' => 'information_id'),
            'like'        => array('polymorphic' => true, 'field' => 'like_ref'),
            'friend'      => array('polymorphic' => true, 'field' => 'friend_ref', 'discriminator_field' => 'name', 'discriminator_map' => array(
                'au' => 'Model\Author',
                'ct' => 'Model\Category',
                'us' => 'Model\User',
            )),
        ),
        'references_many' => array(
            'categories' => array('class' => 'Model\Category', 'field' => 'category_ids'),
            'related'    => array('polymorphic' => true, 'field' => 'related_ref'),
            'elements'   => array('polymorphic' => true, 'field' => 'elements_ref', 'discriminator_field' => 'type', 'discriminator_map' => array(
                'element'  => 'Model\FormElement',
                'textarea' => 'Model\TextareaFormElement',
                'radio'    => 'Model\RadioFormElement',
            )),
        ),
        'relations_many_through' => array(
            'votes_users' => array('class' => 'Model\User', 'through' => 'Model\ArticleVote', 'local' => 'article', 'foreign' => 'user'),
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
    'Model\ArticleInformation' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'relations_one' => array(
            'article' => array('class' => 'Model\Article', 'reference' => 'information'),
        ),
    ),
    'Model\ArticleVote' => array(
        'fields' => array(
        ),
        'references_one' => array(
            'article' => array('class' => 'Model\Article', 'field' => 'article_id'),
            'user'    => array('class' => 'Model\User', 'field' => 'user_id'),
        ),
    ),
    'Model\Author' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'relations_many_one' => array(
            'articles' => array('class' => 'Model\Article', 'reference' => 'author'),
        ),
    ),
    'Model\Category' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'relations_many_many' => array(
            'articles' => array('class' => 'Model\Article', 'reference' => 'categories'),
        ),
    ),
    'Model\Comment' => array(
        'is_embedded' => true,
        'fields' => array(
            'name' => 'string',
            'text' => 'string',
            'note' => 'string',
            'line' => 'string',
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
            'from' => array('alias' => 'desde', 'type' => 'string'),
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
    'Model\User' => array(
        'fields' => array(
            'username' => 'string',
        ),
    ),
    // reference to same class
    'Model\Message' => array(
        'fields' => array(
            'author' => 'string',
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
    // single inheritance
    'Model\FormElement' => array(
        'inheritable' => array('type' => 'single'),
        'fields' => array(
            'label'   => 'string',
            'default' => 'raw',
        ),
    ),
    'Model\TextareaFormElement' => array(
        'inheritance' => array('class' => 'Model\FormElement', 'value' => 'textarea'),
        'fields' => array(
            'default' => 'string',
        ),
    ),
    'Model\RadioFormElement' => array(
        'inheritance' => array('class' => 'Model\FormElement', 'value' => 'radio'),
        'fields' => array(
            'options' => 'serialized',
        ),
    ),
    'Model\OptionableFormElement' => array(
        'inheritance' => array('class' => 'Model\FormElement', 'value' => 'optionable'),
        'fields' => array(
            'options' => 'serialized',
        ),
    ),
    'Model\SelectFormElement' => array(
        'inheritance' => array('class' => 'Model\OptionableFormElement', 'value' => 'select'),
        'fields' => array(
            'select_field' => 'string',
        ),
    ),
    'Model\CheckboxFormElement' => array(
        'inheritance' => array('class' => 'Model\OptionableFormElement', 'value' => 'checkbox'),
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
