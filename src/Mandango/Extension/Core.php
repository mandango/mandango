<?php

/*
 * Copyright 2010 Pablo Díez <pablodip@gmail.com>
 *
 * This file is part of Mandango.
 *
 * Mandango is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mandango is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mandango. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mandango\Extension;

use Mandango\Mondator\Extension;
use Mandango\Mondator\Definition;
use Mandango\Mondator\Definition\Method;
use Mandango\Mondator\Definition\Property;
use Mandango\Mondator\Output;
use Mandango\Type\Container as TypeContainer;
use Mandango\Inflector;

/**
 * Core extension.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Core extends Extension
{
    /**
     * {@inheritdoc}
     */
    protected function setup()
    {
        $this->addRequiredOptions(array(
            'metadata_class',
            'metadata_output',
        ));

        $this->addOptions(array(
            'default_output'    => null,
            'default_behaviors' => array(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function doNewClassExtensionsProcess()
    {
        // default behaviors
        foreach ($this->getOption('default_behaviors') as $behavior) {
            if (!empty($configClass['is_embedded']) && !empty($behavior['not_with_embeddeds'])) {
                continue;
            }
            $this->newClassExtensions[] = $this->createClassExtensionFromArray($behavior);
        }

        // class behaviors
        if (isset($this->configClass['behaviors'])) {
            foreach ($this->configClass['behaviors'] as $behavior) {
                $this->newClassExtensions[] = $this->createClassExtensionFromArray($behavior);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doConfigClassProcess()
    {
        $this->initIsEmbeddedProcess();

        $this->initMandangoProcess();
        if (!$this->configClass['is_embedded']) {
            $this->initConnectionNameProcess();
            $this->initCollectionNameProcess();
        }
        $this->initIndexesProcess();

        $this->initFieldsProcess();
        $this->initReferencesProcess();
        $this->initEmbeddedsProcess();
        if (!$this->configClass['is_embedded']) {
            $this->initRelationsProcess();
        }

        $this->initEventsProcess();
        $this->initIsFileProcess();
    }

    /**
     * {@inheritdoc}
     */
    protected function doClassProcess()
    {
        // parse and check
        $this->parseAndCheckFieldsProcess();
        $this->parseAndCheckReferencesProcess();
        $this->parseAndCheckEmbeddedsProcess();
        if (!$this->configClass['is_embedded']) {
            $this->parseAndCheckRelationsProcess();
        }
        $this->checkDataNamesProcess();

        // definitions
        $this->initDefinitionsProcess();

        // document
        $this->documentConstructorMethodProcess();
        $this->documentMandangoMethodProcess();

        $this->documentSetDocumentDataMethodProcess();
        $this->documentFieldsProcess();
        $this->documentReferencesOneProcess();
        $this->documentReferencesManyProcess();
        if ($this->configClass['_has_references']) {
            $this->documentUpdateReferenceFieldsMethodProcess();
            $this->documentSaveReferencesMethodProcess();
        }
        $this->documentEmbeddedsOneProcess();
        $this->documentEmbeddedsManyProcess();
        if (!$this->configClass['is_embedded']) {
            $this->documentRelationsOneProcess();
            $this->documentRelationsManyOneProcess();
            $this->documentRelationsManyManyProcess();
            $this->documentRelationsManyThroughProcess();
        }
        $this->documentSetMethodProcess();
        $this->documentGetMethodProcess();
        $this->documentFromArrayMethodProcess();
        $this->documentToArrayMethodProcess();
        $this->documentEventsMethodsProcess();
        $this->documentQueryForSaveMethodProcess();

        // repository
        if (!$this->configClass['is_embedded']) {
            $this->repositoryDocumentClassPropertyProcess();
            $this->repositoryIsFilePropertyProcess();
            $this->repositoryConnectionNamePropertyProcess();
            $this->repositoryCollectionNamePropertyProcess();

            $this->repositorySaveMethodProcess();
            $this->repositoryDeleteMethodProcess();
            $this->repositoryEnsureIndexesMethodProcess();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doPreGlobalProcess()
    {
        $this->globalHasReferencesProcess();
        $this->globalIndexesProcess();
    }

    /**
     * {@inheritdoc}
     */
    protected function doPostGlobalProcess()
    {
        $this->globalMetadataProcess();
    }

    /*
     * configClass
     */
    protected function initIsEmbeddedProcess()
    {
        if (isset($this->configClass['is_embedded'])) {
            if (!is_bool($this->configClass['is_embedded'])) {
                throw new \RuntimeException(sprintf('The "is_embedded" of the class "%s" is not a boolean.', $this->class));
            }
        } else {
            $this->configClass['is_embedded'] = false;
        }
    }

    protected function initMandangoProcess()
    {
        if (!isset($this->configClass['mandango'])) {
            $this->configClass['mandango'] = null;
        }
    }

    protected function initConnectionNameProcess()
    {
        if (!isset($this->configClass['connection'])) {
            $this->configClass['connection'] = null;
        }
    }

    protected function initCollectionNameProcess()
    {
        if (!isset($this->configClass['collection'])) {
            $this->configClass['collection'] = str_replace('\\', '_', Inflector::underscore($this->class));
        }
    }

    protected function initFieldsProcess()
    {
        if (!isset($this->configClass['fields'])) {
            $this->configClass['fields'] = array();
        }
    }

    protected function initReferencesProcess()
    {
        if (!isset($this->configClass['references_one'])) {
            $this->configClass['references_one'] = array();
        }
        if (!isset($this->configClass['references_many'])) {
            $this->configClass['references_many'] = array();
        }
    }

    protected function initEmbeddedsProcess()
    {
        if (!isset($this->configClass['embeddeds_one'])) {
            $this->configClass['embeddeds_one'] = array();
        }
        if (!isset($this->configClass['embeddeds_many'])) {
            $this->configClass['embeddeds_many'] = array();
        }
    }

    protected function initRelationsProcess()
    {
        if (!isset($this->configClass['relations_one'])) {
            $this->configClass['relations_one'] = array();
        }
        if (!isset($this->configClass['relations_many_one'])) {
            $this->configClass['relations_many_one'] = array();
        }
        if (!isset($this->configClass['relations_many_many'])) {
            $this->configClass['relations_many_many'] = array();
        }
        if (!isset($this->configClass['relations_many_through'])) {
            $this->configClass['relations_many_through'] = array();
        }
    }

    protected function initIndexesProcess()
    {
        if (!isset($this->configClass['indexes'])) {
            $this->configClass['indexes'] = array();
        }
    }

    protected function initEventsProcess()
    {
        foreach (array(
            'preInsert',
            'postInsert',
            'preUpdate',
            'postUpdate',
            'preDelete',
            'postDelete',
        ) as $event) {
            if (!isset($this->configClass['events']) || !isset($this->configClass['events'][$event])) {
                $this->configClass['events'][$event] = array();
            }
        }

        if (!isset($this->configClass['events'])) {
            $this->configClass['events'] = array();
        }
    }

    protected function initIsFileProcess()
    {
        if (isset($this->configClass['is_file'])) {
            if (!is_bool($this->configClass['is_file'])) {
                throw new \RuntimeException(sprintf('The "is_file" of the class "%s" is not a boolean.', $this->class));
            }
        } else {
            $this->configClass['is_file'] = false;
        }
    }

    /*
     * class
     */
    protected function parseAndCheckFieldsProcess()
    {
        foreach ($this->configClass['fields'] as $name => &$field) {
            if (is_string($field)) {
                $field = array('type' => $field);
            }
        }
        unset($field);

        foreach ($this->configClass['fields'] as $name => $field) {
            if (!is_array($field)) {
                throw new \RuntimeException(sprintf('The field "%s" of the class "%s" is not a string or array.', $name, $this->class));
            }
            if (!isset($field['type'])) {
                throw new \RuntimeException(sprintf('The field "%s" of the class "%s" does not have type.', $name, $this->class));
            }
            if (!TypeContainer::has($field['type'])) {
                throw new \RuntimeException(sprintf('The type "%s" of the field "%s" of the class "%s" does not exists.', $field['type'], $name, $this->class));
            }
        }
    }

    protected function parseAndCheckReferencesProcess()
    {
        // one
        foreach ($this->configClass['references_one'] as $name => &$reference) {
            $this->parseAndCheckAssociationClass($reference, $name);

            if (!isset($reference['field'])) {
                $reference['field'] = Inflector::fieldForClass($reference['class']);
            }
        }

        // many
        foreach ($this->configClass['references_many'] as $name => &$reference) {
            $this->parseAndCheckAssociationClass($reference, $name);

            if (!isset($reference['field'])) {
                $reference['field'] = Inflector::pluralFieldForClass($reference['class']);
            }
        }
    }

    protected function parseAndCheckEmbeddedsProcess()
    {
        // one
        foreach ($this->configClass['embeddeds_one'] as $name => &$embedded) {
            $this->parseAndCheckAssociationClass($embedded, $name);
        }

        // many
        foreach ($this->configClass['embeddeds_many'] as $name => &$embedded) {
            $this->parseAndCheckAssociationClass($embedded, $name);
        }
    }

    protected function parseAndCheckRelationsProcess()
    {
        // one
        foreach ($this->configClass['relations_one'] as $name => &$relation) {
            $this->parseAndCheckAssociationClass($relation, $name);

            if (!isset($relation['field'])) {
                $relation['field'] = Inflector::fieldForClass($this->class);
            }
        }

        // many_one
        foreach ($this->configClass['relations_many_one'] as $name => &$relation) {
            $this->parseAndCheckAssociationClass($relation, $name);

            if (!isset($relation['field'])) {
                $relation['field'] = Inflector::fieldForClass($this->class);
            }
        }

        // many_many
        foreach ($this->configClass['relations_many_many'] as $name => &$relation) {
            $this->parseAndCheckAssociationClass($relation, $name);

            if (!isset($relation['field'])) {
                $relation['field'] = Inflector::pluralFieldForClass($this->class);
            }
        }

        // many_through
        foreach ($this->configClass['relations_many_through'] as $name => &$relation) {
            if (!is_array($relation)) {
                throw new \RuntimeException(sprintf('The relation_many_through "%s" of the class "%s" is not an array.', $name, $this->class));
            }
            if (!isset($relation['class'])) {
                throw new \RuntimeException(sprintf('The relation_many_through "%s" of the class "%s" does not have class.', $name, $this->class));
            }
            if (!isset($relation['through'])) {
                throw new \RuntimeException(sprintf('The relation_many_through "%s" of the class "%s" does not have through.', $name, $this->class));
            }

            if (!isset($relation['local'])) {
                $relation['local'] = Inflector::fieldForClass($this->class);
            }
            if (!isset($relation['foreign'])) {
                $relation['foreign'] = Inflector::fieldForClass($relation['class']);
            }
        }
    }

    protected function checkDataNamesProcess()
    {
        foreach (array_merge(
            array_keys($this->configClass['fields']),
            array_keys($this->configClass['references_one']),
            array_keys($this->configClass['references_many']),
            array_keys($this->configClass['embeddeds_one']),
            array_keys($this->configClass['embeddeds_many']),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_one']) : array(),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_many_one']) : array(),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_many_many']) : array(),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_many_through']) : array()
        ) as $name) {
            if (in_array($name, array('mandango', 'repository', 'collection', 'id', 'query_for_save', 'fields_modified', 'document_data'))) {
                throw new \RuntimeException(sprintf('The document cannot be a data with the name "%s".', $name));
            }
        }
    }

    protected function initDefinitionsProcess()
    {
        $classes = array('document' => $this->class);
        if (false !== $pos = strrpos($classes['document'], '\\')) {
            $documentNamespace = substr($classes['document'], 0, $pos);
            $documentClassName = substr($classes['document'], $pos + 1);
            $classes['document_base']   = $documentNamespace.'\\Base\\'.$documentClassName;
            $classes['repository']      = $documentNamespace.'\\'.$documentClassName.'Repository';
            $classes['repository_base'] = $documentNamespace.'\\Base\\'.$documentClassName.'Repository';
        } else {
            $classes['document_base']   = 'Base'.$classes['document'];
            $classes['repository']      = $classes['document'].'Repository';
            $classes['repository_base'] = 'Base'.$classes['document'].'Repository';
        }

        // document
        $dir = $this->getOption('default_output');
        if (isset($this->configClass['output'])) {
            $dir = $this->configClass['output'];
        }
        if (!$dir) {
            throw new \RuntimeException(sprintf('The document of the class "%s" does not have output.', $this->class));
        }
        $output = new Output($dir);

        $this->definitions['document'] = $definition = new Definition($classes['document'], $output);
        $definition->setParentClass('\\'.$classes['document_base']);
        $definition->setDocComment(<<<EOF
/**
 * {$this->class} document.
 */
EOF
        );

        // document_base
        $output = new Output($this->definitions['document']->getOutput()->getDir().'/Base', true);

        $this->definitions['document_base'] = $definition = new Definition($classes['document_base'], $output);
        $definition->setIsAbstract(true);
        if ($this->configClass['is_embedded']) {
            $definition->setParentClass('\Mandango\Document\EmbeddedDocument');
        } else {
            $definition->setParentClass('\Mandango\Document\Document');
        }
        $definition->setDocComment(<<<EOF
/**
 * Base class of {$this->class} document.
 */
EOF
        );

        if (!$this->configClass['is_embedded']) {
            // repository
            $dir = $this->getOption('default_output');
            if (isset($this->configClass['output'])) {
                $dir = $this->configClass['output'];
            }
            if (!$dir) {
                throw new \RuntimeException(sprintf('The repository of the class "%s" does not have output.', $this->class));
            }
            $output = new Output($dir);

            $this->definitions['repository'] = $definition = new Definition($classes['repository'], $output);
            $definition->setParentClass('\\'.$classes['repository_base']);
            $definition->setDocComment(<<<EOF
/**
 * Repository of {$this->class} document.
 */
EOF
            );

            // repository_base
            $output = new Output($this->definitions['repository']->getOutput()->getDir().'/Base', true);

            $this->definitions['repository_base'] = $definition = new Definition($classes['repository_base'], $output);
            $definition->setIsAbstract(true);
            $definition->setParentClass('\\Mandango\\Repository');
            $definition->setDocComment(<<<EOF
/**
 * Base class of repository of {$this->class} document.
 */
EOF
            );
        }
    }

    protected function documentConstructorMethodProcess()
    {
        $code = '';
        foreach ($this->configClass['fields'] as $name => $field) {
            if (isset($field['default'])) {
                $setter = 'set'.Inflector::camelize($name);
                $default = var_export($field['default'], true);
                $code .= <<<EOF
        \$this->$setter($default);

EOF;
            }
        }

        if ($code) {
            $method = new Method('public', '__construct', '', $code);
            $method->setDocComment(<<<EOF
    /**
     * Constructor.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentMandangoMethodProcess()
    {
        $mandango = '';
        if ($this->configClass['mandango']) {
            $mandango = "'".$this->configClass['mandango']."'";
        }

        $method = new Method('public', 'mandango', '', <<<EOF
        return \Mandango\Container::get($mandango);
EOF
        );
        $method->setIsStatic(true);
        $method->setDocComment(<<<EOF
    /**
     * Returns the mandango of the document.
     *
     * @return Mandango\Mandango The mandango of the document.
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    protected function documentSetDocumentDataMethodProcess()
    {
        // _id
        $idCode = <<<EOF
        if (isset(\$data['_id'])) {
            \$this->id = \$data['_id'];
        }
EOF;
        if ($this->configClass['is_embedded']) {
            $idCode = '';
        }

        // _query_hash
        $queryHashCode = <<<EOF
        if (isset(\$data['_query_hash'])) {
            \$this->addQueryHash(\$data['_query_hash']);
        }
EOF;

        // fields
        $fieldsCode = array();
        $forzeClean = false;
        foreach ($this->configClass['fields'] as $name => $field) {
            $typeCode = strtr(TypeContainer::get($field['type'])->toPHPInString(), array(
                '%from%' => "\$data['$name']",
                '%to%'   => "\$this->data['fields']['$name']",
            ));
            $typeCode = str_replace("\n", "\n            ", $typeCode);

            $fieldsCode[] = <<<EOF
        if (isset(\$data['$name'])) {
            $typeCode
        } elseif (isset(\$data['_fields']['$name'])) {
            \$this->data['fields']['$name'] = null;
        }
EOF;

            if (isset($field['default'])) {
                $forzeClean = true;
            }
        }
        $fieldsCode = implode("\n", $fieldsCode);
        $forzeClean = $forzeClean ? 'true || ' : '';

        // embeddeds one
        $embeddedsOneCode = array();
        foreach ($this->configClass['embeddeds_one'] as $name => $embedded) {
            if (!$this->configClass['is_embedded']) {
                $rap = <<<EOF
            \$embedded->setRootAndPath(\$this, '$name');
EOF;
            } else {
                $rap = <<<EOF

            if (\$rap = \$this->getRootAndPath()) {
                \$embedded->setRootAndPath(\$rap['root'], \$rap['path'].'.$name');
            }
EOF;
            }

            $embeddedsOneCode[] = <<<EOF
        if (isset(\$data['$name'])) {
            \$embedded = new \\{$embedded['class']}();
$rap
            if (isset(\$data['_fields']['$name'])) {
                \$data['$name']['_fields'] = \$data['_fields']['$name'];
            }
            \$embedded->setDocumentData(\$data['$name']);
            \$this->data['embeddeds_one']['$name'] = \$embedded;
        }
EOF;
        }
        $embeddedsOneCode = implode("\n", $embeddedsOneCode);

        // embeddeds many
        $embeddedsManyCode = array();
        foreach ($this->configClass['embeddeds_many'] as $name => $embedded) {
            if (!$this->configClass['is_embedded']) {
                $rap = <<<EOF
            \$embedded->setRootAndPath(\$this, '$name');
EOF;
            } else {
                $rap = <<<EOF
            if (\$rap = \$this->getRootAndPath()) {
                \$embedded->setRootAndPath(\$rap['root'], \$rap['path'].'.$name');
            }
EOF;
            }

            $embeddedsManyCode[] = <<<EOF
        if (isset(\$data['$name'])) {
            \$embedded = new \Mandango\Group\EmbeddedGroup('{$embedded['class']}');
$rap
            \$embedded->setSavedData(\$data['$name']);
            \$this->data['embeddeds_many']['$name'] = \$embedded;
        }
EOF;
        }
        $embeddedsManyCode = implode("\n", $embeddedsManyCode);

        $method = new Method('public', 'setDocumentData', '$data, $clean = false', <<<EOF
        if ($forzeClean\$clean) {
            \$this->data = array();
            \$this->fieldsModified = array();
        }

$idCode

$queryHashCode

$fieldsCode
$embeddedsOneCode
$embeddedsManyCode

        return \$this;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Set the document data (hydrate).
     *
     * @param array \$data  The document data.
     * @param bool  \$clean Whether clean the document.
     *
     * @return {$this->class} The document (fluent interface).
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    protected function documentFieldsProcess()
    {
        foreach ($this->configClass['fields'] as $name => $field) {
            // setter
            if (!$this->configClass['is_embedded']) {
                $isNotNewCode = "null !== \$this->id";
            } else {
                $isNotNewCode = "(\$rap = \$this->getRootAndPath()) && !\$rap['root']->isNew()";
            }
            $getter = 'get'.Inflector::camelize($name);
            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', <<<EOF
        if (!isset(\$this->data['fields']['$name'])) {
            if ($isNotNewCode) {
                \$this->$getter();
                if (\$value === \$this->data['fields']['$name']) {
                    return \$this;
                }
            } else {
                if (null === \$value) {
                    return \$this;
                }
                \$this->fieldsModified['$name'] = null;
                \$this->data['fields']['$name'] = \$value;
                return \$this;
            }
        } elseif (\$value === \$this->data['fields']['$name']) {
            return \$this;
        }

        if (!isset(\$this->fieldsModified['$name']) && !array_key_exists('$name', \$this->fieldsModified)) {
            \$this->fieldsModified['$name'] = \$this->data['fields']['$name'];
        } elseif (\$value === \$this->fieldsModified['$name']) {
            unset(\$this->fieldsModified['$name']);
        }

        \$this->data['fields']['$name'] = \$value;

        return \$this;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Set the "$name" field.
     *
     * @param mixed \$value The value.
     *
     * @return {$this->class} The document (fluent interface).
     */
EOF
            );

            $this->definitions['document_base']->addMethod($method);

            // getter
            $typeCode = strtr(TypeContainer::get($field['type'])->toPHPInString(), array(
                '%to%' => "\$this->data['fields']['$name']",
            ));
            if (!$this->configClass['is_embedded']) {
                $typeCode = str_replace('%from%', "\$data['$name']", $typeCode);
                $queryCode = <<<EOF
            if (\$this->isNew()) {
                \$this->data['fields']['$name'] = null;
            } elseif (!isset(\$this->data['fields']) || !array_key_exists('$name', \$this->data['fields'])) {
                \$this->addFieldCache('$name');
                \$data = static::collection()->findOne(array('_id' => \$this->id), array('$name' => 1));
                if (isset(\$data['$name'])) {
                    $typeCode
                } else {
                    \$this->data['fields']['$name'] = null;
                }
            }
EOF;
            } else {
                $typeCode = str_replace('%from%', "\$data", $typeCode);
                $queryCode = <<<EOF
            if (
                (!isset(\$this->data['fields']) || !array_key_exists('$name', \$this->data['fields']))
                &&
                (\$rap = \$this->getRootAndPath())
                &&
                !\$this->isEmbeddedOneChangedInParent()
            ) {
                \$field = \$rap['path'].'.$name';
                \$rap['root']->addFieldCache(\$field);
                \$collection = call_user_func(array(get_class(\$rap['root']), 'collection'));
                \$data = \$collection->findOne(array('_id' => \$rap['root']->getId()), array(\$field => 1));
                foreach (explode('.', \$field) as \$key) {
                    if (!isset(\$data[\$key])) {
                        \$data = null;
                        break;
                    }
                    \$data = \$data[\$key];
                }
                if (null !== \$data) {
                    $typeCode
                }
            }
            if (!isset(\$this->data['fields']['$name'])) {
                \$this->data['fields']['$name'] = null;
            }
EOF;
            }
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        if (!isset(\$this->data['fields']['$name'])) {
$queryCode
        }

        return \$this->data['fields']['$name'];
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" field.
     *
     * @return mixed The $name field.
     */
EOF
            );

            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentReferencesOneProcess()
    {
        foreach ($this->configClass['references_one'] as $name => $reference) {
            $fieldSetter = 'set'.Inflector::camelize($reference['field']);
            $fieldGetter = 'get'.Inflector::camelize($reference['field']);

            // setter
            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', <<<EOF
        if (null !== \$value && !\$value instanceof \\{$reference['class']}) {
            throw new \InvalidArgumentException('The "$name" reference is not an instance of {$reference['class']}.');
        }

        \$this->$fieldSetter((null === \$value || \$value->isNew()) ? null : \$value->getId());

        \$this->data['references_one']['$name'] = \$value;

        return \$this;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Set the "$name" reference.
     *
     * @param {$reference['class']}|null \$value The reference, or null.
     *
     * @return {$this->class} The document (fluent interface).
     *
     * @throws \InvalidArgumentException If the class is not an instance of {$reference['class']}.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);

            // getter
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        if (!isset(\$this->data['references_one']['$name'])) {
            if (!\$id = \$this->$fieldGetter()) {
                return null;
            }
            if (!\$document = \\{$reference['class']}::find(\$id)) {
                throw new \RuntimeException('The reference "$name" does not exist.');
            }
            \$this->data['references_one']['$name'] = \$document;
        }

        return \$this->data['references_one']['$name'];
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" reference.
     *
     * @return {$reference['class']}|null The reference or null if it does not exist.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentReferencesManyProcess()
    {
        foreach ($this->configClass['references_many'] as $name => $reference) {
            // getter
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        if (!isset(\$this->data['references_many']['$name'])) {
            \$this->data['references_many']['$name'] = new \Mandango\Group\ReferenceGroup('{$reference['class']}', \$this, '{$reference['field']}');
        }

        return \$this->data['references_many']['$name'];
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" reference.
     *
     * @return Mandango\Group\ReferenceGroup The reference.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentUpdateReferenceFieldsMethodProcess()
    {
        $referencesCode = array();
        // references one
        foreach ($this->configClass['references_one'] as $name => $reference) {
            $fieldSetter = 'set'.Inflector::camelize($reference['field']);

            $referencesCode[] = <<<EOF
        if (isset(\$this->data['references_one']['$name']) && !isset(\$this->data['fields']['{$reference['field']}'])) {
            \$this->$fieldSetter(\$this->data['references_one']['$name']->getId());
        }
EOF;
        }
        // references many
        foreach ($this->configClass['references_many'] as $name => $reference) {
            $fieldSetter = 'set'.Inflector::camelize($reference['field']);
            $fieldGetter = 'get'.Inflector::camelize($reference['field']);

            $referencesCode[] = <<<EOF
        if (isset(\$this->data['references_many']['$name'])) {
            \$group = \$this->data['references_many']['$name'];
            \$add = \$group->getAdd();
            \$remove = \$group->getRemove();
            if (\$add || \$remove) {
                \$ids = \$this->$fieldGetter();
                foreach (\$add as \$document) {
                    \$ids[] = \$document->getId();
                }
                foreach (\$remove as \$document) {
                    unset(\$ids[array_search(\$document->getId(), \$ids)]);
                }
                \$this->$fieldSetter(\$ids ? array_values(\$ids) : null);
            }
        }
EOF;
        }
        $referencesCode = implode("\n", $referencesCode);

        $embeddedsCode = array();
        // embeddeds one
        foreach ($this->configClass['embeddeds_one'] as $name => $embedded) {
            if (!$this->configClasses[$embedded['class']]['_has_references']) {
                continue;
            }

            $embeddedsCode[] = <<<EOF
        if (isset(\$this->data['embeddeds_one']['$name'])) {
            \$this->data['embeddeds_one']['$name']->updateReferenceFields();
        }
EOF;
        }
        // embeddeds many
        foreach ($this->configClass['embeddeds_many'] as $name => $embedded) {
            if (!$this->configClasses[$embedded['class']]['_has_references']) {
                continue;
            }

            $embeddedsCode[] = <<<EOF
        if (isset(\$this->data['embeddeds_many']['$name'])) {
            \$group = \$this->data['embeddeds_many']['$name'];
            foreach (\$group->saved() as \$document) {
                \$document->updateReferenceFields();
            }
        }
EOF;
        }
        $embeddedsCode = implode("\n", $embeddedsCode);

        $method = new Method('public', 'updateReferenceFields', '', <<<EOF
$referencesCode
$embeddedsCode
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Update the value of the reference fields.
     */
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    protected function documentSaveReferencesMethodProcess()
    {
        // references one
        $referencesOneCode = array();
        foreach ($this->configClass['references_one'] as $name => $reference) {
            $referencesOneCode[] = <<<EOF
        if (isset(\$this->data['references_one']['$name'])) {
            \$this->data['references_one']['$name']->save();
        }
EOF;
        }
        $referencesOneCode = implode("\n", $referencesOneCode);

        // references many
        $referencesManyCode = array();
        foreach ($this->configClass['references_many'] as $name => $reference) {
            $referencesManyCode[] = <<<EOF
        if (isset(\$this->data['references_many']['$name'])) {
            \$group = \$this->data['references_many']['$name'];
            \$documents = array();
            foreach (\$group->getAdd() as \$document) {
                \$documents[] = \$document;
            }
            if (\$group->isSavedInitialized()) {
                foreach (\$group->saved() as \$document) {
                    \$documents[] = \$document;
                }
            }
            if (\$documents) {
                \\{$reference['class']}::repository()->save(\$documents);
            }
        }
EOF;
        }
        $referencesManyCode = implode("\n", $referencesManyCode);

        // embeddeds one
        $embeddedsOneCode = array();
        foreach ($this->configClass['embeddeds_one'] as $name => $reference) {
            $embeddedsOneCode[] = <<<EOF
        if (isset(\$this->data['embeddeds_one']['$name'])) {
            \$this->data['embeddeds_one']['$name']->saveReferences();
        }
EOF;
        }
        $embeddedsOneCode = implode("\n", $embeddedsOneCode);

        $method = new Method('public', 'saveReferences', '', <<<EOF
$referencesOneCode
$referencesManyCode
$embeddedsOneCode
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Save the references.
     */
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    protected function documentEmbeddedsOneProcess()
    {
        foreach ($this->configClass['embeddeds_one'] as $name => $embedded) {
            // setter
            $rootDocument = !$this->configClass['is_embedded'] ? '$this' : '$this->getRootDocument()';

            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', <<<EOF
        if (null !== \$value && !\$value instanceof \\{$embedded['class']}) {
            throw new \InvalidArgumentException('The "$name" embedded one is not an instance of {$embedded['class']}');
        }
        if (null !== \$value) {
            if (\$this instanceof \Mandango\Document\Document) {
                \$value->setRootAndPath(\$this, '$name');
            } elseif (\$rap = \$this->getRootAndPath()) {
                \$value->setRootAndPath(\$rap['root'], \$rap['path'].'.$name');
            }
        }

        if (!\Mandango\Archive::has(\$this, 'embedded_one.$name')) {
            \$originalValue = isset(\$this->data['embeddeds_one']['$name']) ? \$this->data['embeddeds_one']['$name'] : null;
            \Mandango\Archive::set(\$this, 'embedded_one.$name', \$originalValue);
        } elseif (\Mandango\Archive::get(\$this, 'embedded_one.$name') === \$value) {
            \Mandango\Archive::remove(\$this, 'embedded_one.$name');
        }

        \$this->data['embeddeds_one']['$name'] = \$value;

        return \$this;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Set the "$name" embeded one.
     *
     * @param {$embedded['class']}|null \$value The "$name" embedded one.
     *
     * @return {$this->class} The document (fluent interface).
     *
     * @throws \InvalidArgumentException If the value is not an instance of {$embedded['class']} or null.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);

            // getter
            if (!$this->configClass['is_embedded']) {
                $queryCode = <<<EOF
            if (\$this->isNew()) {
                \$this->data['embeddeds_one']['$name'] = null;
            } elseif (!isset(\$this->data['embeddeds_one']) || !array_key_exists('$name', \$this->data['embeddeds_one'])) {
                \$exists = static::collection()->findOne(array('_id' => \$this->id, '$name' => array('\$exists' => 1)));
                if (\$exists) {
                    \$embedded = new \\{$embedded['class']}();
                    \$embedded->setRootAndPath(\$this, '$name');
                    \$this->data['embeddeds_one']['$name'] = \$embedded;
                } else {
                    \$this->data['embeddeds_one']['$name'] = null;
                }
            }
EOF;
            } else {
                $queryCode = <<<EOF
            if (
                (!isset(\$this->data['embeddeds_one']) || !array_key_exists('$name', \$this->data['embeddeds_one']))
                &&
                (\$rap = \$this->getRootAndPath())
                &&
                !\$this->isEmbeddedOneChangedInParent()
                &&
                false === strpos(\$rap['path'], '._add')
            ) {
                \$collection = call_user_func(array(get_class(\$rap['root']), 'collection'));
                \$field = \$rap['path'].'.$name';
                \$result = \$collection->findOne(array('_id' => \$rap['root']->getId(), \$field => array('\$exists' => 1)));
                if (\$result) {
                    \$embedded = new \\{$embedded['class']}();
                    \$embedded->setRootAndPath(\$rap['root'], \$field);
                    \$this->data['embeddeds_one']['$name'] = \$embedded;
                }
            }
            if (!isset(\$this->data['embeddeds_one']['$name'])) {
                \$this->data['embeddeds_one']['$name'] = null;
            }
EOF;
            }
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        if (!isset(\$this->data['embeddeds_one']['$name'])) {
$queryCode
        }

        return \$this->data['embeddeds_one']['$name'];
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" embedded one.
     *
     * @return {$embedded['class']}|null The "$name" embedded one.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentEmbeddedsManyProcess()
    {
        foreach ($this->configClass['embeddeds_many'] as $name => $embedded) {
            if (!$this->configClass['is_embedded']) {
                $rootAndPath = <<<EOF
            \$embedded->setRootAndPath(\$this, '$name');
EOF;
            } else {
                $rootAndPath = <<<EOF
            if (\$rap = \$this->getRootAndPath()) {
                \$embedded->setRootAndPath(\$rap['root'], \$rap['path'].'.$name');
            }
EOF;
            }

            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        if (!isset(\$this->data['embeddeds_many']['$name'])) {
            \$this->data['embeddeds_many']['$name'] = \$embedded = new \Mandango\Group\EmbeddedGroup('{$embedded['class']}');
$rootAndPath
        }

        return \$this->data['embeddeds_many']['$name'];
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" embedded many.
     *
     * @return Mandango\Group\EmbeddedGroup The "$name" embedded many.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentRelationsOneProcess()
    {
        foreach ($this->configClass['relations_one'] as $name => $relation) {
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        return \\{$relation['class']}::query(array('{$relation['field']}' => \$this->getId()))->one();
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" relation one.
     *
     * @return {$relation['class']} The "$name" relation one.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentRelationsManyOneProcess()
    {
        foreach ($this->configClass['relations_many_one'] as $name => $relation) {
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        return \\{$relation['class']}::query(array('{$relation['field']}' => \$this->getId()));
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" relation many-one.
     *
     * @return {$relation['class']} The "$name" relation many-one.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentRelationsManyManyProcess()
    {
        foreach ($this->configClass['relations_many_many'] as $name => $relation) {
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        return \\{$relation['class']}::query(array('{$relation['field']}' => \$this->getId()));
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" relation many-many.
     *
     * @return {$relation['class']} The "$name" relation many-many.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentRelationsManyThroughProcess()
    {
        foreach ($this->configClass['relations_many_through'] as $name => $relation) {
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        \$ids = array();
        foreach (\\{$relation['through']}::collection()
            ->find(array('{$relation['local']}' => \$this->getId()), array('{$relation['foreign']}' => 1))
        as \$value) {
            \$ids[] = \$value['{$relation['foreign']}'];
        }

        return \\{$relation['class']}::query(array('_id' => array('\$in' => \$ids)));
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" relation many-through.
     *
     * @return {$relation['class']} The "$name" relation many-through.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentSetMethodProcess()
    {
        $setCode = array();
        foreach (array_merge(
            array_keys($this->configClass['fields']),
            array_keys($this->configClass['references_one']),
            array_keys($this->configClass['embeddeds_one'])
        ) as $name) {
            $setter = 'set'.Inflector::camelize($name);

            $setCode[] = <<<EOF
        if ('$name' == \$name) {
            return \$this->$setter(\$value);
        }
EOF;
        }
        $setCode = implode("\n", $setCode);

        // method
        $method = new Method('public', 'set', '$name, $value', <<<EOF
$setCode

        throw new \InvalidArgumentException(sprintf('The document data "%s" is not valid.', \$name));
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Set a document data value by data name as string.
     *
     * @param string \$name  The data name.
     * @param mixed  \$vaule The value.
     *
     * @return mixed the data name setter return value.
     *
     * @throws \InvalidArgumentException If the data name is not valid.
     */
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    protected function documentGetMethodProcess()
    {
        $getCode = array();
        foreach (array_merge(
            array_keys($this->configClass['fields']),
            array_keys($this->configClass['references_one']),
            array_keys($this->configClass['references_many']),
            array_keys($this->configClass['embeddeds_one']),
            array_keys($this->configClass['embeddeds_many'])
        ) as $name) {
            $getter = 'get'.Inflector::camelize($name);

            $getCode[] = <<<EOF
        if ('$name' === \$name) {
            return \$this->$getter();
        }
EOF;
        }
        $getCode = implode("\n", $getCode);

        // method
        $method = new Method('public', 'get', '$name', <<<EOF
$getCode

        throw new \InvalidArgumentException(sprintf('The document data "%s" is not valid.', \$name));
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns a document data by data name as string.
     *
     * @param string \$name The data name.
     *
     * @return mixed The data name getter return value.
     *
     * @throws \InvalidArgumentException If the data name is not valid.
     */
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    protected function documentFromArrayMethodProcess()
    {
        // fields
        $fieldsCode = array();
        foreach ($this->configClass['fields'] as $name => $field) {
            $setter = 'set'.Inflector::camelize($name);
            $fieldsCode[] = <<<EOF
        if (isset(\$array['$name'])) {
            \$this->$setter(\$array['$name']);
        }
EOF;
        }
        $fieldsCode = "\n".implode("\n", $fieldsCode)."\n";

        // embeddeds one
        $embeddedsOneCode = array();
        foreach ($this->configClass['embeddeds_one'] as $name => $embedded) {
            $setter = 'set'.Inflector::camelize($name);
            $embeddedsOneCode[] = <<<EOF
        if (isset(\$array['$name'])) {
            \$embedded = new \\{$embedded['class']}();
            \$embedded->fromArray(\$array['$name']);
            \$this->$setter(\$embedded);
        }
EOF;
        }
        $embeddedsOneCode = "\n".implode("\n", $embeddedsOneCode)."\n";

        // embeddeds many
        $embeddedsManyCode = array();
        foreach ($this->configClass['embeddeds_many'] as $name => $embedded) {
            $getter = 'get'.Inflector::camelize($name);
            $embeddedsManyCode[] = <<<EOF
        if (isset(\$array['$name'])) {
            \$embeddeds = array();
            foreach (\$array['$name'] as \$documentData) {
                \$embeddeds[] = \$embedded = new \\{$embedded['class']}();
                \$embedded->setDocumentData(\$documentData);
            }
            \$this->$getter()->replace(\$embeddeds);
        }
EOF;
        }
        $embeddedsManyCode = "\n".implode("\n", $embeddedsManyCode)."\n";

        $method = new Method('public', 'fromArray', 'array $array', <<<EOF
$fieldsCode
$embeddedsOneCode
$embeddedsManyCode
        return \$this;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Imports data from an array.
     *
     * @param array \$data An array.
     *
     * @return {$this->class} The document (fluent interface).
     */
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    protected function documentToArrayMethodProcess()
    {
        $fieldsCode = array();
        foreach ($this->configClass['fields'] as $name => $field) {
            $fieldsCode[] = <<<EOF
        if (isset(\$this->data['fields']['$name'])) {
            \$array['$name'] = \$this->data['fields']['$name'];
        }
EOF;
        }
        $fieldsCode = "\n".implode("\n", $fieldsCode)."\n";

        $method = new Method('public', 'toArray', '', <<<EOF
        \$array = array();
$fieldsCode
        return \$array;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Export the document data to an array.
     *
     * @return array An array with the document data.
     */
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    protected function documentEventsMethodsProcess()
    {
        foreach ($this->configClass['events'] as $event => $methods) {
            if (!$methods) {
                continue;
            }

            $eventMethodName = $event.'Event';
            $eventMethodCode = '';

            // methods
            $methodsCode = array();
            foreach ($methods as $methodName) {
                $methodsCode[] = <<<EOF
        \$this->$methodName();
EOF;
            }
            if ($methodsCode) {
                $eventMethodCode .= implode("\n", $methodsCode)."\n";
            }

            // method
            $method = new Method('public', $eventMethodName, '', $eventMethodCode);
            $method->setDocComment(<<<EOF
    /**
     * INTERNAL. Invoke the "$event" event.
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    protected function documentQueryForSaveMethodProcess()
    {
        // fields
        $fieldsCode = '';
        if ($this->configClass['fields']) {
            $fieldsInsertCode = array();
            $fieldsUpdateCode = array();
            foreach ($this->configClass['fields'] as $name => $field) {
                $typeCode = strtr(TypeContainer::get($field['type'])->toMongoInString(), array(
                    '%from%' => "\$this->data['fields']['$name']",
                ));

                // insert
                $insertTypeCode = str_replace('%to%', "\$query['$name']", $typeCode);
                $fieldsInsertCode[] = <<<EOF
                if (isset(\$this->data['fields']['$name'])) {
                    $insertTypeCode
                }
EOF;

                // update
                if (!$this->configClass['is_embedded']) {
                    $updateTypeCode = str_replace('%to%', "\$query['\$set']['$name']", $typeCode);
                    $fieldUpdateSetCode = <<<EOF
                            $updateTypeCode
EOF;
                    $fieldUpdateUnsetCode = <<<EOF
                            \$query['\$unset']['$name'] = 1;
EOF;
                } else {
                    $updateTypeCode = str_replace('%to%', "\$query['\$set'][\$documentPath.'.$name']", $typeCode);
                    $fieldUpdateSetCode = <<<EOF
                            $updateTypeCode
EOF;
                    $fieldUpdateUnsetCode = <<<EOF
                            \$query['\$unset'][\$documentPath.'.$name'] = 1;
EOF;
                }
                $fieldsUpdateCode[] = <<<EOF
                if (isset(\$this->data['fields']['$name']) || array_key_exists('$name', \$this->data['fields'])) {
                    \$value = \$this->data['fields']['$name'];
                    \$originalValue = \$this->getOriginalFieldValue('$name');
                    if (\$value !== \$originalValue) {
                        if (null !== \$value) {
$fieldUpdateSetCode
                        } else {
$fieldUpdateUnsetCode
                        }
                    }
                }
EOF;
            }
            $fieldsInsertCode = implode("\n", $fieldsInsertCode);
            $fieldsUpdateCode = implode("\n", $fieldsUpdateCode);

            if ($this->configClass['is_embedded']) {
                $fieldsInsertCode = <<<EOF
                \$rootQuery = \$query;
                \$query =& \$rootQuery;
                \$rap = \$this->getRootAndPath();
                if (true === \$reset) {
                    \$path = array('\$set', \$rap['path']);
                } elseif ('deep' == \$reset) {
                    \$path = explode('.', '\$set.'.\$rap['path']);
                } else {
                    \$path = explode('.', \$rap['path']);
                }
                foreach (\$path as \$name) {
                    if (0 === strpos(\$name, '_add')) {
                        \$name = substr(\$name, 4);
                    }
                    if (!isset(\$query[\$name])) {
                        \$query[\$name] = array();
                    }
                    \$query =& \$query[\$name];
                }

$fieldsInsertCode

                unset(\$query);
                \$query = \$rootQuery;
EOF;
                $fieldsUpdateCode = <<<EOF
                \$rap = \$this->getRootAndPath();
                \$documentPath = \$rap['path'];

$fieldsUpdateCode
EOF;
            }

            $fieldsCode = <<<EOF
        if (isset(\$this->data['fields'])) {
            if (\$isNew || \$reset) {
$fieldsInsertCode
            } else {
$fieldsUpdateCode
            }
        }
EOF;
        }

        // references
        $referencesCode = '';
        if ($this->configClass['_has_references'] && !$this->configClass['is_embedded']) {
            $referencesCode = <<<EOF
        \$this->updateReferenceFields();
EOF;
        }

        // embeddeds one
        $embeddedsOneCode = '';
        if ($this->configClass['embeddeds_one']) {
            $embeddedsOneCode = array();
            foreach ($this->configClass['embeddeds_one'] as $name => $embedded) {
                $embeddedsOneCode[] = <<<EOF
            \$originalValue = \$this->getOriginalEmbeddedOneValue('$name');
            if (isset(\$this->data['embeddeds_one']['$name'])) {
                \$resetValue = \$reset ? \$reset : (!\$isNew && \$this->data['embeddeds_one']['$name'] !== \$originalValue);
                \$query = \$this->data['embeddeds_one']['$name']->queryForSave(\$query, \$isNew, \$resetValue);
            } elseif (array_key_exists('$name', \$this->data['embeddeds_one'])) {
                if (\$originalValue) {
                    \$rap = \$originalValue->getRootAndPath();
                    \$query['\$unset'][\$rap['path']] = 1;
                }
            }
EOF;
            }

            $embeddedsOneCode = implode("\n", $embeddedsOneCode);
            $embeddedsOneCode = <<<EOF
        if (isset(\$this->data['embeddeds_one'])) {
$embeddedsOneCode
        }
EOF;
        }

        // embeddeds many
        $embeddedsManyCode = '';
        if ($this->configClass['embeddeds_many']) {
            $embeddedsManyInsertCode = array();
            $embeddedsManyUpdateCode = array();
            foreach ($this->configClass['embeddeds_many'] as $name => $embedded) {
                $embeddedsManyInsertCode[] = <<<EOF
                if (isset(\$this->data['embeddeds_many']['$name'])) {
                    foreach (\$this->data['embeddeds_many']['$name']->getAdd() as \$document) {
                        \$query = \$document->queryForSave(\$query, \$isNew);
                    }
                }
EOF;
                $embeddedsManyUpdateCode[] = <<<EOF
                if (isset(\$this->data['embeddeds_many']['$name'])) {
                    \$group = \$this->data['embeddeds_many']['$name'];
                    foreach (\$group->saved() as \$document) {
                        \$query = \$document->queryForSave(\$query, \$isNew);
                    }
                    \$groupRap = \$group->getRootAndPath();
                    foreach (\$group->getAdd() as \$document) {
                        \$q = \$document->queryForSave(array(), true);
                        \$rap = \$document->getRootAndPath();
                        foreach (explode('.', \$rap['path']) as \$name) {
                            if (0 === strpos(\$name, '_add')) {
                                \$name = substr(\$name, 4);
                            }
                            \$q = \$q[\$name];
                        }
                        \$query['\$pushAll'][\$groupRap['path']][] = \$q;
                    }
                    foreach (\$group->getRemove() as \$document) {
                        \$rap = \$document->getRootAndPath();
                        \$query['\$unset'][\$rap['path']] = 1;
                    }
                }
EOF;
            }
            $embeddedsManyInsertCode = implode("\n", $embeddedsManyInsertCode);
            $embeddedsManyUpdateCode = implode("\n", $embeddedsManyUpdateCode);

            $embeddedsManyCode = <<<EOF
        if (isset(\$this->data['embeddeds_many'])) {
            if (\$isNew) {
$embeddedsManyInsertCode
            } else {
$embeddedsManyUpdateCode
            }
        }
EOF;
        }

        // document or embedded
        if (!$this->configClass['is_embedded']) {
            $arguments = '';
            $codeHeader = <<<EOF
        \$query = array();
        \$isNew = \$this->isNew();
        \$reset = false;
EOF;
        } else {
            $arguments = '$query, $isNew, $reset = false';
            $codeHeader = <<<EOF
EOF;
        }

        $method = new Method('public', 'queryForSave', $arguments, <<<EOF
$codeHeader

$referencesCode
$fieldsCode
        if (true === \$reset) {
            \$reset = 'deep';
        }
$embeddedsOneCode
$embeddedsManyCode

        return \$query;
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    protected function repositoryDocumentClassPropertyProcess()
    {
        $property = new Property('protected', 'documentClass', $this->class);
        $this->definitions['repository_base']->addProperty($property);
    }

    protected function repositoryIsFilePropertyProcess()
    {
        $property = new Property('protected', 'isFile', $this->configClass['is_file']);
        $this->definitions['repository_base']->addProperty($property);
    }

    protected function repositoryConnectionNamePropertyProcess()
    {
        $property = new Property('protected', 'connectionName', $this->configClass['connection']);
        $this->definitions['repository_base']->addProperty($property);
    }

    protected function repositoryCollectionNamePropertyProcess()
    {
        $property = new Property('protected', 'collectionName', $this->configClass['collection']);
        $this->definitions['repository_base']->addProperty($property);
    }

    protected function repositorySaveMethodProcess()
    {
        // references
        $updateReferencesCode = '';
        $checkReferencesChangedCode = '';
        if ($this->configClass['_has_references']) {
            $updateReferencesCode = <<<EOF
            \$document->saveReferences();
            \$document->updateReferenceFields();


EOF;

            $checkReferencesChangedCode = <<<EOF
                if (!\$document->isModified()) {
                    continue;
                }


EOF;
        }

        // events
        $preInsert = '';
        if ($this->configClass['events']['preInsert']) {
            $preInsert = "\$document->preInsertEvent();\n                ";
        }

        $postInsert = '';
        if ($this->configClass['events']['postInsert']) {
            $postInsert = "\n                    \$document->postInsertEvent();";
        }

        $preUpdate = '';
        if ($this->configClass['events']['preUpdate']) {
            $preUpdate = "\$document->preUpdateEvent();\n                ";
        }

        $postUpdate = '';
        if ($this->configClass['events']['postUpdate']) {
            $postUpdate = "\n                \$document->postUpdateEvent();";
        }

        // method
        $method = new Method('public', 'save', '$documents', <<<EOF
        if (!is_array(\$documents)) {
            \$documents = array(\$documents);
        }

        \$identityMap =& \$this->identityMap->allByReference();
        \$collection = \$this->collection();

        \$inserts = array();
        \$updates = array();
        foreach (\$documents as \$document) {
            $updateReferencesCode if (\$document->isNew()) {
                \$inserts[spl_object_hash(\$document)] = \$document;
            } else {
                \$updates[] = \$document;
            }
        }

        // insert
        if (\$inserts) {
            \$a = array();
            foreach (\$inserts as \$oid => \$document) {
                $checkReferencesChangedCode$preInsert\$a[\$oid] = \$document->queryForSave();
            }

            if (\$a) {
                \$collection->batchInsert(\$a);

                foreach (\$a as \$oid => \$data) {
                    \$document = \$inserts[\$oid];

                    \$document->setId(\$data['_id']);
                    \$document->clearModified();
                    \$identityMap[\$data['_id']->__toString()] = \$document;$postInsert
                }
            }
        }

        // updates
        foreach (\$updates as \$document) {
            if (\$query = \$document->queryForSave()) {
                $preUpdate\$collection->update(array('_id' => \$document->getId()), \$query);
                \$document->clearModified();$postUpdate
            }
        }
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Save documents.
     *
     * @param mixed \$documents A document or an array of documents.
     */
EOF
        );
        $this->definitions['repository_base']->addMethod($method);
    }

    protected function repositoryDeleteMethodProcess()
    {
        // events
        $preDelete = '';
        if ($this->configClass['events']['preDelete']) {
            $preDelete = "\$document->preDeleteEvent();\n                ";
        }

        $postDelete = '';
        if ($this->configClass['events']['postDelete']) {
            $postDelete = <<<EOF


        foreach (\$documents as \$document) {
            \$document->postDeleteEvent();
        }
EOF;
        }

        // methods
        $method = new Method('public', 'delete', '$documents', <<<EOF
        if (!is_array(\$documents)) {
            \$documents = array(\$documents);
        }

        \$identityMap =& \$this->identityMap->allByReference();

        \$ids = array();
        foreach (\$documents as \$document) {
            $preDelete\$ids[] = \$id = \$document->getAndRemoveId();
            unset(\$identityMap[\$id->__toString()]);
        }

        \$this->collection()->remove(array('_id' => array('\$in' => \$ids)));$postDelete
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Delete documents.
     *
     * @param mixed \$documents A document or an array of documents.
     */
EOF
        );
        $this->definitions['repository_base']->addMethod($method);
    }

    protected function repositoryEnsureIndexesMethodProcess()
    {
        $indexesCode = array();
        foreach ($this->configClass['_indexes'] as $name => $index) {
            $keys    = \Mandango\Mondator\Dumper::exportArray($index['keys'], 12);
            $options = \Mandango\Mondator\Dumper::exportArray(array_merge(isset($index['options']) ? $index['options'] : array(), array('safe' => true)), 12);

            $indexesCode[] = <<<EOF
        \$this->collection()->ensureIndex($keys, $options);
EOF;
        }
        $indexesCode = implode("\n", $indexesCode);

        $method = new Method('public', 'ensureIndexes', '', $indexesCode);
        $method->setDocComment(<<<EOF
    /**
     * Ensure the inexes.
     */
EOF
        );
        $this->definitions['repository_base']->addMethod($method);
    }

    /*
     * preGlobal
     */
    protected function globalHasReferencesProcess()
    {
        do {
             $continue = false;
             foreach ($this->configClasses as $class => $configClass) {
                 if (isset($configClass['_has_references'])) {
                     continue;
                 }

                 $hasReferences = false;
                 if ($configClass['references_one'] || $configClass['references_many']) {
                     $hasReferences = true;
                 }
                 foreach (array_merge($configClass['embeddeds_one'], $configClass['embeddeds_many']) as $name => $embedded) {
                     if (!isset($this->configClasses[$embedded['class']]['_has_references'])) {
                         $continue = true;
                         continue 2;
                     }
                     if ($this->configClasses[$embedded['class']]['_has_references']) {
                         $hasReferences = true;
                     }
                 }
                 $configClass['_has_references'] = $hasReferences;
             }
         } while ($continue);
    }

    protected function globalIndexesProcess()
    {
        do {
            $continue = false;
            foreach ($this->configClasses as $class => $configClass) {
                if (isset($configClass['_indexes'])) {
                    continue;
                }

                $indexes = $configClass['indexes'];
                foreach (array_merge($configClass['embeddeds_one'], $configClass['embeddeds_many']) as $name => $embedded) {
                    if (!isset($this->configClasses[$embedded['class']]['_indexes'])) {
                        $continue = true;
                        continue 2;
                    }
                    $embeddedIndexes = array();
                    foreach ($this->configClasses[$embedded['class']]['_indexes'] as $index) {
                        $newKeys = array();
                        foreach ($index['keys'] as $keyName => $value) {
                            $newKeys[$name.'.'.$keyName] = $value;
                        }
                        $index['keys'] = $newKeys;
                        $embeddedIndexes[] = $index;
                    }
                    $indexes = array_merge($indexes, $embeddedIndexes);
                }
                $configClass['_indexes'] = $indexes;
            }
        } while ($continue);
    }

    /*
     * postGlobal
     */
    protected function globalMetadataProcess()
    {
        $output = new Output($this->getOption('metadata_output'), true);
        $definition = new Definition($this->getOption('metadata_class'), $output);
        $definition->setParentClass('\Mandango\Metadata');
        $this->definitions['metadata'] = $definition;

        $output = new Output($this->getOption('metadata_output'), true);
        $definition = new Definition($this->getOption('metadata_class').'Info', $output);
        $this->definitions['metadata_info'] = $definition;

        $classes = array();
        foreach ($this->configClasses as $class => $configClass) {
            $classes[$class] = $configClass['is_embedded'];

            $info = array();
            // general
            $info['is_embedded'] = $configClass['is_embedded'];
            if (!$info['is_embedded']) {
                $info['mandango'] = $configClass['mandango'];
                $info['connection'] = $configClass['connection'];
                $info['collection'] = $configClass['collection'];
            }
            // fields
            $info['fields'] = $configClass['fields'];
            // references
            $info['references_one'] = $configClass['references_one'];
            $info['references_many'] = $configClass['references_many'];
            // embeddeds
            $info['embeddeds_one'] = $configClass['embeddeds_one'];
            $info['embeddeds_many'] = $configClass['embeddeds_many'];
            // relations
            if (!$info['is_embedded']) {
                $info['relations_one'] = $configClass['relations_one'];
                $info['relations_many_one'] = $configClass['relations_many_one'];
                $info['relations_many_many'] = $configClass['relations_many_many'];
                $info['relations_many_through'] = $configClass['relations_many_through'];
            }
            // indexes
            if (!$info['is_embedded']) {
                $info['indexes'] = $configClass['indexes'];
            }

            $info = \Mandango\Mondator\Dumper::exportArray($info, 12);

            $method = new Method('public', 'get'.str_replace('\\', '', $class).'ClassInfo', '', <<<EOF
        return $info;
EOF
            );
            $this->definitions['metadata_info']->addMethod($method);
        }

        $property = new Property('protected', 'classes', $classes);
        $this->definitions['metadata']->addProperty($property);
    }

    protected function parseAndCheckAssociationClass(&$association, $name)
    {
        if (is_string($association)) {
            $association = array('class' => $association);
        }

        if (!is_array($association)) {
            throw new \RuntimeException(sprintf('The association "%s" of the class "%s" is not an array or string.', $name, $this->class));
        }
        if (!isset($association['class'])) {
            throw new \RuntimeException(sprintf('The association "%s" of the class "%s" does not have class.', $name, $this->class));
        }
        if (!is_string($association['class'])) {
            throw new \RuntimeException(sprintf('The class of the association "%s" of the class "%s" is not an string.', $name, $this->class));
        }
    }
}
