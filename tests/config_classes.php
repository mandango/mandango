<?php

return array(
    'Model\Article' => array(
        'useBatchInsert' => true,
        'collection' => 'articles',
        'fields' => array(
            'title'    => 'string',
            'content'  => 'string',
            'note'     => 'string',
            'line'     => 'string',
            'text'     => 'string',
            'isActive' => 'boolean',
            'date'     => 'date',
            'database' => array('dbName' => 'basatos', 'type' => 'string'),
        ),
        'embeddedsOne' => array(
            'source'          => array('class' => 'Model\Source'),
            'simpleEmbedded' => array('class' => 'Model\SimpleEmbedded'),
        ),
        'embeddedsMany' => array(
            'comments' => array('class' => 'Model\Comment'),
        ),
        'referencesOne' => array(
            'author'      => array('class' => 'Model\Author', 'field' => 'authorId', 'onDelete' => 'cascade'),
            'information' => array('class' => 'Model\ArticleInformation', 'field' => 'informationId', 'onDelete' => 'unset'),
            'like'        => array('polymorphic' => true, 'field' => 'likeRef', 'onDelete' => 'cascade'),
            'likeUnset'   => array('polymorphic' => true, 'onDelete' => 'unset'),
            'friend'      => array('polymorphic' => true, 'field' => 'friendRef', 'onDelete' => 'cascade', 'discriminatorField' => 'name', 'discriminatorMap' => array(
                'au' => 'Model\Author',
                'ct' => 'Model\Category',
                'us' => 'Model\User',
            )),
            'friendUnset' => array('polymorphic' => true, 'onDelete' => 'unset', 'discriminatorField' => 'name', 'discriminatorMap' => array(
                'au' => 'Model\Author',
                'ct' => 'Model\Category',
                'us' => 'Model\User',
            )),
        ),
        'referencesMany' => array(
            'categories' => array('class' => 'Model\Category', 'field' => 'categoryIds', 'onDelete' => 'unset'),
            'related'    => array('polymorphic' => true, 'field' => 'relatedRef', 'onDelete' => 'unset'),
            'elements'   => array('polymorphic' => true, 'field' => 'elementsRef', 'discriminatorField' => 'type', 'discriminatorMap' => array(
                'element'  => 'Model\FormElement',
                'textarea' => 'Model\TextareaFormElement',
                'radio'    => 'Model\RadioFormElement',
            )),
        ),
        'relationsManyThrough' => array(
            'votesUsers' => array('class' => 'Model\User', 'through' => 'Model\ArticleVote', 'local' => 'article', 'foreign' => 'user'),
        ),
        'indexes' => array(
            array(
                'keys'    => array('slug' => 1),
                'options' => array('unique' => true),
            ),
            array(
                'keys' => array('authorId' => 1, 'isActive' => 1),
            ),
        ),
    ),
    'Model\ArticleInformation' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'relationsOne' => array(
            'article' => array('class' => 'Model\Article', 'reference' => 'information'),
        ),
    ),
    'Model\ArticleVote' => array(
        'fields' => array(
        ),
        'referencesOne' => array(
            'article' => array('class' => 'Model\Article', 'field' => 'articleId'),
            'user'    => array('class' => 'Model\User', 'field' => 'userId'),
        ),
    ),
    'Model\Author' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'relationsManyOne' => array(
            'articles' => array('class' => 'Model\Article', 'reference' => 'author'),
        ),
    ),
    'Model\Category' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'relationsManyMany' => array(
            'articles' => array('class' => 'Model\Article', 'reference' => 'categories'),
        ),
    ),
    'Model\Comment' => array(
        'isEmbedded' => true,
        'fields' => array(
            'name' => 'string',
            'text' => 'string',
            'note' => 'string',
            'line' => 'string',
        ),
        'referencesOne' => array(
            'author' => array('class' => 'Model\Author', 'field' => 'authorId'),
        ),
        'referencesMany' => array(
            'categories' => array('class' => 'Model\Category', 'field' => 'categoryIds'),
        ),
        'embeddedsMany' => array(
            'infos' => array('class' => 'Model\Info'),
        ),
        'indexes' => array(
            array(
                'keys'    => array('line' => 1),
                'options' => array('unique' => true),
            ),
            array(
                'keys' => array('authorId' => 1, 'note' => 1),
            ),
        ),
    ),
    'Model\Info' => array(
        'isEmbedded' => true,
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
        'isEmbedded' => true,
        'fields' => array(
            'name' => 'string',
            'text' => 'string',
            'note' => 'string',
            'line' => 'string',
            'from' => array('dbName' => 'desde', 'type' => 'string'),
        ),
        'referencesOne' => array(
            'author' => array('class' => 'Model\Author', 'field' => 'authorId'),
        ),
        'referencesMany' => array(
            'categories' => array('class' => 'Model\Category', 'field' => 'categoryIds'),
        ),
        'embeddedsOne' => array(
            'info' => array('class' => 'Model\Info'),
        ),
        'indexes' => array(
            array(
                'keys'    => array('name' => 1),
                'options' => array('unique' => true),
            ),
            array(
                'keys' => array('authorId' => 1, 'line' => 1),
            ),
        ),
    ),
    'Model\User' => array(
        'fields' => array(
            'username' => 'string',
        ),
    ),
    'Model\SimpleEmbedded' => array(
        'isEmbedded' => true,
        'fields' => array(
            'name' => 'string',
        ),
    ),
    // reference to same class
    'Model\Message' => array(
        'fields' => array(
            'author' => 'string',
        ),
        'referencesOne' => array(
            'replyTo' => array('class' => 'Model\Message', 'field' => 'replyToId'),
        ),
    ),
    // default values
    'Model\Book' => array(
        'fields' => array(
            'title'   => 'string',
            'comment' => array('type' => 'string', 'default' => 'good'),
            'isHere'  => array('type' => 'boolean', 'default' => true),
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
        'embeddedsOne' => array(
            'embedded' => array('class' => 'Model\EmbeddedEvents'),
        ),
    ),
    'Model\EventsEmbeddedMany' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'embeddedsMany' => array(
            'embedded' => array('class' => 'Model\EmbeddedEvents'),
        ),
    ),
    'Model\EmbeddedEvents' => array(
        'isEmbedded' => true,
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
    'Model\InitializeArgs' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'referencesOne' => array(
            'author' => array('class' => 'Model\Author'),
        ),
    ),
    // gridfs
    'Model\Image' => array(
        'isFile' => true,
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
    'Model\Element' => array(
        'inheritable' => array('type' => 'single'),
        'fields' => array(
          'label'   => 'string',
        ),
        'referencesMany' => array(
            'categories' => array('class' => 'Model\Category'),
        ),
        'embeddedsOne' => array(
            'source' => array('class' => 'Model\Source'),
        ),
        'events' => array(
            'preInsert'  => array('elementPreInsert'),
            'postInsert' => array('elementPostInsert'),
            'preUpdate'  => array('elementPreUpdate'),
            'postUpdate' => array('elementPostUpdate'),
            'preDelete'  => array('elementPreDelete'),
            'postDelete' => array('elementPostDelete'),
        ),
    ),
    'Model\TextElement' => array(
        'inheritable' => array('type' => 'single'),
        'inheritance' => array('class' => 'Model\Element', 'value' => 'textelement'),
        'fields' => array(
          'htmltext'   => 'string',
        ),
    ),
    'Model\TextTextElement' => array(
        'inheritance' => array('class' => 'Model\TextElement', 'value' => 'texttextelement'),
        'fields' => array(
            'text_text' => 'string',
        ),
        'events' => array(
            'preInsert' => array('textTextElementPreInsert'),
        ),
    ),
    'Model\FormElement' => array(
        'inheritable' => array('type' => 'single'),
        'inheritance' => array('class' => 'Model\Element', 'value' => 'formelement'),
        'fields' => array(
            'default' => 'raw',
        ),
        'referencesOne' => array(
            'author' => array('class' => 'Model\Author'),
        ),
        'events' => array(
            'preInsert'  => array('formElementPreInsert'),
            'postInsert' => array('formElementPostInsert'),
            'preUpdate'  => array('formElementPreUpdate'),
            'postUpdate' => array('formElementPostUpdate'),
            'preDelete'  => array('formElementPreDelete'),
            'postDelete' => array('formElementPostDelete'),
        ),
        'embeddedsMany' => array(
            'comments' => array('class' => 'Model\Comment'),
        ),
    ),
    'Model\TextareaFormElement' => array(
        'inheritance' => array('class' => 'Model\FormElement', 'value' => 'textarea'),
        'fields' => array(
            'default' => 'string',
        ),
        'events' => array(
            'preInsert'  => array('textareaPreInsert'),
            'postInsert' => array('textareaPostInsert'),
            'preUpdate'  => array('textareaPreUpdate'),
            'postUpdate' => array('textareaPostUpdate'),
            'preDelete'  => array('textareaPreDelete'),
            'postDelete' => array('textareaPostDelete'),
        ),
    ),
    'Model\RadioFormElement' => array(
        'inheritance' => array('class' => 'Model\FormElement', 'value' => 'radio'),
        'fields' => array(
            'options' => 'serialized',
        ),
        'referencesOne' => array(
            'authorLocal' => array('class' => 'Model\Author'),
        ),
        'referencesMany' => array(
            'categoriesLocal' => array('class' => 'Model\Category'),
        ),
        'embeddedsOne' => array(
            'sourceLocal' => array('class' => 'Model\Source'),
        ),
        'embeddedsMany' => array(
            'commentsLocal' => array('class' => 'Model\Comment'),
        ),
    ),
    // id generators
    'Model\NoneIdGenerator' => array(
        'idGenerator' => 'none',
        'fields' => array(
            'name' => 'string',
        )
    ),
    'Model\NativeIdGenerator' => array(
        'idGenerator' => 'native',
        'fields' => array(
            'name' => 'string',
        )
    ),
    'Model\SequenceIdGenerator' => array(
        'idGenerator' => 'sequence',
        'fields' => array(
            'name' => 'string',
        )
    ),
    'Model\SequenceIdGenerator2' => array(
        'idGenerator' => 'sequence',
        'fields' => array(
            'name' => 'string',
        )
    ),
    'Model\SequenceIdGeneratorDescending' => array(
        'idGenerator' => array('name' => 'sequence', 'options' => array('increment' => -1)),
        'fields' => array(
            'name' => 'string',
        )
    ),
    'Model\SequenceIdGeneratorStart' => array(
        'idGenerator' => array('name' => 'sequence', 'options' => array('start' => 2000)),
        'fields' => array(
            'name' => 'string',
        )
    ),
    'Model\IdGeneratorSingleInheritanceGrandParent' => array(
        'inheritable' => array('type' => 'single'),
        'idGenerator' => 'sequence',
        'fields' => array(
            'name' => 'string',
        ),
    ),
    'Model\IdGeneratorSingleInheritanceParent' => array(
        'inheritance' => array('class' => 'Model\IdGeneratorSingleInheritanceGrandParent', 'value' => 'parent'),
        'inheritable' => array('type' => 'single'),
    ),
    'Model\IdGeneratorSingleInheritanceChild' => array(
        'inheritance' => array('class' => 'Model\IdGeneratorSingleInheritanceParent', 'value' => 'child'),
    ),
);
