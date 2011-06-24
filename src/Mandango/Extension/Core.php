<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Extension;

use Mandango\Mondator\Extension;
use Mandango\Mondator\Definition;
use Mandango\Mondator\Definition\Method;
use Mandango\Mondator\Definition\Property;
use Mandango\Mondator\Output;
use Mandango\Type\Container as TypeContainer;

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
            'metadata_factory_class',
            'metadata_factory_output',
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
            if (!empty($configClass['isEmbedded']) && !empty($behavior['not_with_embeddeds'])) {
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

        $this->initInheritableProcess();
        $this->initInheritanceProcess();

        $this->initMandangoProcess();
        if (!$this->configClass['isEmbedded']) {
            $this->initConnectionNameProcess();
            $this->initCollectionNameProcess();
        }
        $this->initIndexesProcess();

        $this->initFieldsProcess();
        $this->initReferencesProcess();
        $this->initEmbeddedsProcess();
        if (!$this->configClass['isEmbedded']) {
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
        if (!$this->configClass['isEmbedded']) {
            $this->parseAndCheckRelationsProcess();
        }
        $this->checkDataNamesProcess();

        // definitions
        $this->initDefinitionsProcess();

        // document
        $this->documentInitializeDefaultsMethodProcess();

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
        if (!$this->configClass['isEmbedded']) {
            $this->documentRelationsOneProcess();
            $this->documentRelationsManyOneProcess();
            $this->documentRelationsManyManyProcess();
            $this->documentRelationsManyThroughProcess();
        }
        if ($this->configClass['_has_groups']) {
            $this->documentResetGroupsProcess();
        }
        $this->documentSetMethodProcess();
        $this->documentGetMethodProcess();
        $this->documentFromArrayMethodProcess();
        $this->documentToArrayMethodProcess();
        $this->documentEventsMethodsProcess();
        $this->documentQueryForSaveMethodProcess();

        if (!$this->configClass['isEmbedded']) {
            // repository
            $this->repositoryDocumentClassPropertyProcess();
            $this->repositoryIsFilePropertyProcess();
            $this->repositoryConnectionNamePropertyProcess();
            $this->repositoryCollectionNamePropertyProcess();

            $this->repositoryCountMethodProcess();
            $this->repositoryRemoveMethodProcess();

            $this->repositorySaveMethodProcess();
            $this->repositoryDeleteMethodProcess();
            $this->repositoryEnsureIndexesMethodProcess();

            // query
            $this->queryAllMethodProcess();
            $this->queryCreateCursorMethodProcess();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doPreGlobalProcess()
    {
        $this->globalInheritableAndInheritanceProcess();
        $this->globalHasReferencesProcess();
        $this->globalHasGroupsProcess();
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
    private function initInheritableProcess()
    {
        if (!isset($this->configClass['inheritable'])) {
            $this->configClass['inheritable'] = false;
        } elseif ($this->configClass['isEmbedded']) {
            throw new \RuntimeException(sprintf('Using unheritance in a embedded document "%s".', $this->class));
        }
    }

    private function initInheritanceProcess()
    {
        if (!isset($this->configClass['inheritance'])) {
            $this->configClass['inheritance'] = false;
        } elseif ($this->configClass['isEmbedded']) {
            throw new \RuntimeException(sprintf('Using unheritance in a embedded document "%s".', $this->class));
        }
    }

    private function initIsEmbeddedProcess()
    {
        if (isset($this->configClass['isEmbedded'])) {
            if (!is_bool($this->configClass['isEmbedded'])) {
                throw new \RuntimeException(sprintf('The "isEmbedded" of the class "%s" is not a boolean.', $this->class));
            }
        } else {
            $this->configClass['isEmbedded'] = false;
        }
    }

    private function initMandangoProcess()
    {
        if (!isset($this->configClass['mandango'])) {
            $this->configClass['mandango'] = null;
        }
    }

    private function initConnectionNameProcess()
    {
        if (!isset($this->configClass['connection'])) {
            $this->configClass['connection'] = null;
        }
    }

    private function initCollectionNameProcess()
    {
        if (!isset($this->configClass['collection'])) {
            $this->configClass['collection'] = strtolower(str_replace('\\', '_', $this->class));
        }
    }

    private function initFieldsProcess()
    {
        if (!isset($this->configClass['fields'])) {
            $this->configClass['fields'] = array();
        }
    }

    private function initReferencesProcess()
    {
        if (!isset($this->configClass['referencesOne'])) {
            $this->configClass['referencesOne'] = array();
        }
        if (!isset($this->configClass['referencesMany'])) {
            $this->configClass['referencesMany'] = array();
        }
    }

    private function initEmbeddedsProcess()
    {
        if (!isset($this->configClass['embeddedsOne'])) {
            $this->configClass['embeddedsOne'] = array();
        }
        if (!isset($this->configClass['embeddedsMany'])) {
            $this->configClass['embeddedsMany'] = array();
        }
    }

    private function initRelationsProcess()
    {
        if (!isset($this->configClass['relationsOne'])) {
            $this->configClass['relationsOne'] = array();
        }
        if (!isset($this->configClass['relationsManyOne'])) {
            $this->configClass['relationsManyOne'] = array();
        }
        if (!isset($this->configClass['relationsManyMany'])) {
            $this->configClass['relationsManyMany'] = array();
        }
        if (!isset($this->configClass['relationsManyThrough'])) {
            $this->configClass['relationsManyThrough'] = array();
        }
    }

    private function initIndexesProcess()
    {
        if (!isset($this->configClass['indexes'])) {
            $this->configClass['indexes'] = array();
        }
    }

    private function initEventsProcess()
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

    private function initIsFileProcess()
    {
        if (isset($this->configClass['isFile'])) {
            if (!is_bool($this->configClass['isFile'])) {
                throw new \RuntimeException(sprintf('The "isFile" of the class "%s" is not a boolean.', $this->class));
            }
        } else {
            $this->configClass['isFile'] = false;
        }
    }

    /*
     * class
     */
    private function parseAndCheckFieldsProcess()
    {
        foreach ($this->configClass['fields'] as $name => &$field) {
            if (is_string($field)) {
                $field = array('type' => $field);
            }

            if ($this->configClass['inheritance'] && !isset($field['inherited'])) {
                $field['inherited'] = false;
            }
        }
        unset($field);

        foreach ($this->configClass['fields'] as $name => &$field) {
            if (!is_array($field)) {
                throw new \RuntimeException(sprintf('The field "%s" of the class "%s" is not a string or array.', $name, $this->class));
            }

            if (!isset($field['type'])) {
                throw new \RuntimeException(sprintf('The field "%s" of the class "%s" does not have type.', $name, $this->class));
            }
            if (!TypeContainer::has($field['type'])) {
                throw new \RuntimeException(sprintf('The type "%s" of the field "%s" of the class "%s" does not exists.', $field['type'], $name, $this->class));
            }

            if (!isset($field['dbName'])) {
                $field['dbName'] = $name;
            } elseif (!is_string($field['dbName'])) {
                throw new \RuntimeException(sprintf('The dbName of the field "%s" of the class "%s" is not an string.', $name, $this->class));
            }
        }
        unset($field);
    }

    private function parseAndCheckReferencesProcess()
    {
        // one
        foreach ($this->configClass['referencesOne'] as $name => &$reference) {
            $this->parseAndCheckAssociationClass($reference, $name);

            if ($this->configClass['inheritance'] && !isset($reference['inherited'])) {
                $reference['inherited'] = false;
            }

            if (!isset($reference['field'])) {
                $reference['field'] = $name.'_reference_field';
            }
            $field = array('type' => 'raw', 'dbName' => $name);
            if (!empty($reference['inherited'])) {
                $field['inherited'] = true;
            }
            $this->configClass['fields'][$reference['field']] = $field;
        }

        // many
        foreach ($this->configClass['referencesMany'] as $name => &$reference) {
            $this->parseAndCheckAssociationClass($reference, $name);

            if ($this->configClass['inheritance'] && !isset($reference['inherited'])) {
                $reference['inherited'] = false;
            }

            if (!isset($reference['field'])) {
                $reference['field'] = $name.'_reference_field';
            }
            $field = array('type' => 'raw', 'dbName' => $name);
            if (!empty($reference['inherited'])) {
                $field['inherited'] = true;
            }
            $this->configClass['fields'][$reference['field']] = $field;
        }
    }

    private function parseAndCheckEmbeddedsProcess()
    {
        // one
        foreach ($this->configClass['embeddedsOne'] as $name => &$embedded) {
            $this->parseAndCheckAssociationClass($embedded, $name);

            if ($this->configClass['inheritance'] && !isset($embedded['inherited'])) {
                $embedded['inherited'] = false;
            }
        }

        // many
        foreach ($this->configClass['embeddedsMany'] as $name => &$embedded) {
            $this->parseAndCheckAssociationClass($embedded, $name);

            if ($this->configClass['inheritance'] && !isset($embedded['inherited'])) {
                $embedded['inherited'] = false;
            }
        }
    }

    private function parseAndCheckRelationsProcess()
    {
        // one
        foreach ($this->configClass['relationsOne'] as $name => &$relation) {
            $this->parseAndCheckAssociationClass($relation, $name);

            if (!isset($relation['reference'])) {
                throw new \RuntimeException(sprintf('The relation one "%s" of the class "%s" does not have reference.', $name, $this->class));
            }
        }

        // many_one
        foreach ($this->configClass['relationsManyOne'] as $name => &$relation) {
            $this->parseAndCheckAssociationClass($relation, $name);

            if (!isset($relation['reference'])) {
                throw new \RuntimeException(sprintf('The relation many one "%s" of the class "%s" does not have reference.', $name, $this->class));
            }
        }

        // many_many
        foreach ($this->configClass['relationsManyMany'] as $name => &$relation) {
            $this->parseAndCheckAssociationClass($relation, $name);

            if (!isset($relation['reference'])) {
                throw new \RuntimeException(sprintf('The relation many many "%s" of the class "%s" does not have reference.', $name, $this->class));
            }
        }

        // many_through
        foreach ($this->configClass['relationsManyThrough'] as $name => &$relation) {
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
                throw new \RuntimeException(sprintf('The relation_many_through "%s" of the class "%s" does not have local.', $name, $this->class));
            }
            if (!isset($relation['foreign'])) {
                throw new \RuntimeException(sprintf('The relation_many_through "%s" of the class "%s" does not have foreign.', $name, $this->class));
            }
        }
    }

    private function checkDataNamesProcess()
    {
        foreach (array_merge(
            array_keys($this->configClass['fields']),
            array_keys($this->configClass['referencesOne']),
            array_keys($this->configClass['referencesMany']),
            array_keys($this->configClass['embeddedsOne']),
            array_keys($this->configClass['embeddedsMany']),
            !$this->configClass['isEmbedded'] ? array_keys($this->configClass['relationsOne']) : array(),
            !$this->configClass['isEmbedded'] ? array_keys($this->configClass['relationsManyOne']) : array(),
            !$this->configClass['isEmbedded'] ? array_keys($this->configClass['relationsManyMany']) : array(),
            !$this->configClass['isEmbedded'] ? array_keys($this->configClass['relationsManyThrough']) : array()
        ) as $name) {
            if (in_array($name, array('mandango', 'repository', 'collection', 'id', 'query_for_save', 'fields_modified', 'document_data'))) {
                throw new \RuntimeException(sprintf('The document cannot be a data with the name "%s".', $name));
            }
        }
    }

    private function initDefinitionsProcess()
    {
        $classes = array('document' => $this->class);
        if (false !== $pos = strrpos($classes['document'], '\\')) {
            $documentNamespace = substr($classes['document'], 0, $pos);
            $documentClassName = substr($classes['document'], $pos + 1);
            $classes['document_base']   = $documentNamespace.'\\Base\\'.$documentClassName;
            $classes['repository']      = $documentNamespace.'\\'.$documentClassName.'Repository';
            $classes['repository_base'] = $documentNamespace.'\\Base\\'.$documentClassName.'Repository';
            $classes['query']           = $documentNamespace.'\\'.$documentClassName.'Query';
            $classes['query_base']      = $documentNamespace.'\\Base\\'.$documentClassName.'Query';
        } else {
            $classes['document_base']   = 'Base'.$classes['document'];
            $classes['repository']      = $classes['document'].'Repository';
            $classes['repository_base'] = 'Base'.$classes['document'].'Repository';
            $classes['query']           = $classes['document'].'Query';
            $classes['query_base']      = 'Base'.$classes['document'].'Query';
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

        // document base
        $output = new Output($this->definitions['document']->getOutput()->getDir().'/Base', true);

        $this->definitions['document_base'] = $definition = new Definition($classes['document_base'], $output);
        $definition->setAbstract(true);
        if ($this->configClass['isEmbedded']) {
            $definition->setParentClass('\Mandango\Document\EmbeddedDocument');
        } else {
            if ($this->configClass['inheritance']) {
                $definition->setParentClass('\\'.$this->configClass['inheritance']['class']);
            } else {
                $definition->setParentClass('\Mandango\Document\Document');
            }
        }
        $definition->setDocComment(<<<EOF
/**
 * Base class of {$this->class} document.
 */
EOF
        );

        if (!$this->configClass['isEmbedded']) {
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

            // repository base
            $output = new Output($this->definitions['repository']->getOutput()->getDir().'/Base', true);

            $this->definitions['repository_base'] = $definition = new Definition($classes['repository_base'], $output);
            $definition->setAbstract(true);
            $definition->setParentClass('\\Mandango\\Repository');
            $definition->setDocComment(<<<EOF
/**
 * Base class of repository of {$this->class} document.
 */
EOF
            );

            // query
            $dir = $this->getOption('default_output');
            if (isset($this->configClass['output'])) {
                $dir = $this->configClass['output'];
            }
            if (!$dir) {
                throw new \RuntimeException(sprintf('The query of the class "%s" does not have output.', $this->class));
            }
            $output = new Output($dir);

            $this->definitions['query'] = $definition = new Definition($classes['query'], $output);
            $definition->setParentClass('\\'.$classes['query_base']);
            $definition->setDocComment(<<<EOF
/**
 * Query of {$this->class} document.
 */
EOF
            );

            // query base
            $output = new Output($this->definitions['query']->getOutput()->getDir().'/Base', true);

            $this->definitions['query_base'] = $definition = new Definition($classes['query_base'], $output);
            $definition->setAbstract(true);
            $definition->setParentClass('\\Mandango\\Query');
            $definition->setDocComment(<<<EOF
/**
 * Base class of query of {$this->class} document.
 */
EOF
            );
        }
    }

    private function documentInitializeDefaultsMethodProcess()
    {
        // default values
        $defaultValuesCode = array();
        foreach ($this->configClass['fields'] as $name => $field) {
            if (isset($field['default'])) {
                $setter = 'set'.ucfirst($name);
                $default = var_export($field['default'], true);
                $defaultValuesCode[] = <<<EOF
        \$this->$setter($default);
EOF;
            }
        }
        $defaultValuesCode = implode("\n", $defaultValuesCode);

        $method = new Method('public', 'initializeDefaults', '', <<<EOF
$defaultValuesCode
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Initializes the document defaults.
     */
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    private function documentSetDocumentDataMethodProcess()
    {
        // _id
        $idCode = <<<EOF
        if (isset(\$data['_id'])) {
            \$this->id = \$data['_id'];
        }
EOF;
        if ($this->configClass['isEmbedded']) {
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
            if (!empty($field['inherited'])) {
                continue;
            }
            $typeCode = strtr(TypeContainer::get($field['type'])->toPHPInString(), array(
                '%from%' => "\$data['{$field['dbName']}']",
                '%to%'   => "\$this->data['fields']['$name']",
            ));
            $typeCode = str_replace("\n", "\n            ", $typeCode);

            $fieldsCode[] = <<<EOF
        if (isset(\$data['{$field['dbName']}'])) {
            $typeCode
        } elseif (isset(\$data['_fields']['{$field['dbName']}'])) {
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
        foreach ($this->configClass['embeddedsOne'] as $name => $embedded) {
            if (!empty($embedded['inherited'])) {
                continue;
            }

            if (!$this->configClass['isEmbedded']) {
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
            \$embedded = new \\{$embedded['class']}(\$this->getMandango());
$rap
            if (isset(\$data['_fields']['$name'])) {
                \$data['$name']['_fields'] = \$data['_fields']['$name'];
            }
            \$embedded->setDocumentData(\$data['$name']);
            \$this->data['embeddedsOne']['$name'] = \$embedded;
        }
EOF;
        }
        $embeddedsOneCode = implode("\n", $embeddedsOneCode);

        // embeddeds many
        $embeddedsManyCode = array();
        foreach ($this->configClass['embeddedsMany'] as $name => $embedded) {
            if (!empty($embedded['inherited'])) {
                continue;
            }

            if (!$this->configClass['isEmbedded']) {
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
            \$this->data['embeddedsMany']['$name'] = \$embedded;
        }
EOF;
        }
        $embeddedsManyCode = implode("\n", $embeddedsManyCode);

        // single inheritance
        if ($this->configClass['inheritance'] && 'single' == $this->configClass['inheritance']['type']) {
            $code = <<<EOF
        parent::setDocumentData(\$data, $forzeClean\$clean);

$fieldsCode
$embeddedsOneCode
$embeddedsManyCode

        return \$this;
EOF;
        } else {
            $code = <<<EOF
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
EOF;
        }

        $method = new Method('public', 'setDocumentData', '$data, $clean = false', $code);
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

    private function documentFieldsProcess()
    {
        foreach ($this->configClass['fields'] as $name => $field) {
            if (!empty($field['inherited'])) {
                continue;
            }
            // setter
            if (!$this->configClass['isEmbedded']) {
                $isNotNewCode = "null !== \$this->id";
            } else {
                $isNotNewCode = "(\$rap = \$this->getRootAndPath()) && !\$rap['root']->isNew()";
            }
            $getter = 'get'.ucfirst($name);
            $method = new Method('public', 'set'.ucfirst($name), '$value', <<<EOF
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
            if (!$this->configClass['isEmbedded']) {
                $typeCode = str_replace('%from%', "\$data['{$field['dbName']}']", $typeCode);
                $queryCode = <<<EOF
            if (\$this->isNew()) {
                \$this->data['fields']['$name'] = null;
            } elseif (!isset(\$this->data['fields']) || !array_key_exists('$name', \$this->data['fields'])) {
                \$this->addFieldCache('{$field['dbName']}');
                \$data = \$this->getRepository()->getCollection()->findOne(array('_id' => \$this->id), array('{$field['dbName']}' => 1));
                if (isset(\$data['{$field['dbName']}'])) {
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
                &&
                !\$this->isEmbeddedManyNew()
            ) {
                \$field = \$rap['path'].'.{$field['dbName']}';
                \$rap['root']->addFieldCache(\$field);
                \$collection = \$this->getMandango()->getRepository(get_class(\$rap['root']))->getCollection();
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
            $method = new Method('public', 'get'.ucfirst($name), '', <<<EOF
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

    private function documentReferencesOneProcess()
    {
        foreach ($this->configClass['referencesOne'] as $name => $reference) {
            if (!empty($reference['inherited'])) {
                continue;
            }

            $fieldSetter = 'set'.ucfirst($reference['field']);
            $fieldGetter = 'get'.ucfirst($reference['field']);

            // normal
            if (isset($reference['class'])) {
                // set
                $setCode = <<<EOF
        if (null !== \$value && !\$value instanceof \\{$reference['class']}) {
            throw new \InvalidArgumentException('The "$name" reference is not an instance of {$reference['class']}.');
        }

        \$this->$fieldSetter((null === \$value || \$value->isNew()) ? null : \$value->getId());

        \$this->data['referencesOne']['$name'] = \$value;

        return \$this;
EOF;
                $setDocComment = <<<EOF
    /**
     * Set the "$name" reference.
     *
     * @param {$reference['class']}|null \$value The reference, or null.
     *
     * @return {$this->class} The document (fluent interface).
     *
     * @throws \InvalidArgumentException If the class is not an instance of {$reference['class']}.
     */
EOF;
                // get
                $addReferenceCache = '';
                if (!$this->configClass['isEmbedded']) {
                    $addReferenceCache = <<<EOF
            if (!\$this->isNew()) {
                \$this->addReferenceCache('$name');
            }
EOF;
                }

                $getCode = <<<EOF
        if (!isset(\$this->data['referencesOne']['$name'])) {
            $addReferenceCache
            if (!\$id = \$this->$fieldGetter()) {
                return null;
            }
            if (!\$document = \$this->getMandango()->getRepository('{$reference['class']}')->findOneById(\$id)) {
                throw new \RuntimeException('The reference "$name" does not exist.');
            }
            \$this->data['referencesOne']['$name'] = \$document;
        }

        return \$this->data['referencesOne']['$name'];
EOF;
                $getDocComment = <<<EOF
    /**
     * Returns the "$name" reference.
     *
     * @return {$reference['class']}|null The reference or null if it does not exist.
     */
EOF;
            // polymorphic
            } else {
                $discriminatorField = $reference['discriminatorField'];
                $discriminatorMap = $reference['discriminatorMap'];

                // discriminator map
                if ($discriminatorMap) {
                    $discriminatorMapValues = \Mandango\Mondator\Dumper::exportArray($discriminatorMap, 16);
                    $setDiscriminatorValue = <<<EOF
            if (false === \$discriminatorValue = array_search(get_class(\$value), $discriminatorMapValues)) {
                throw new \InvalidArgumentException(sprintf('The class "%s" is not a possible reference in the reference "$name" of the class "{$this->class}".', get_class(\$value)));
            }
EOF;
                    $getDiscriminatorValue = <<<EOF
            \$discriminatorMapValues = $discriminatorMapValues;
            \$discriminatorValue = \$discriminatorMapValues[\$ref['$discriminatorField']];
EOF;
                } else {
                    $setDiscriminatorValue = <<<EOF
            \$discriminatorValue = get_class(\$value);
EOF;
                    $getDiscriminatorValue = <<<EOF
            \$discriminatorValue = \$ref['$discriminatorField'];
EOF;
                }

                // set
                $setCode = <<<EOF
        if (!\$value instanceof \Mandango\Document\Document) {
            throw new \InvalidArgumentException('The reference is not a Mandango document.');
        }

        if (null === \$value || \$value->isNew()) {
            \$fieldValue = null;
        } else {
$setDiscriminatorValue
            \$fieldValue = array(
                '$discriminatorField' => \$discriminatorValue,
                'id' => \$value->getId(),
            );
        }
        \$this->$fieldSetter(\$fieldValue);

        \$this->data['referencesOne']['$name'] = \$value;

        return \$this;
EOF;
                $setDocComment = <<<EOF
    /**
     * Set the "$name" polymorphic reference.
     *
     * @param Mandango\Document\Document|null \$value The reference, or null.
     *
     * @return {$this->class} The document (fluent interface).
     *
     * @throws \InvalidArgumentException If the class is not an instance of Mandango\Document\Document.
     */
EOF;
                // get
                $getCode = <<<EOF
        if (!isset(\$this->data['referencesOne']['$name'])) {
            if (!\$ref = \$this->$fieldGetter()) {
                return null;
            }
$getDiscriminatorValue
            if (!\$document = \$this->getMandango()->getRepository(\$discriminatorValue)->findOneById(\$ref['id'])) {
                throw new \RuntimeException('The reference "$name" does not exist.');
            }
            \$this->data['referencesOne']['$name'] = \$document;
        }

        return \$this->data['referencesOne']['$name'];
EOF;
                $getDocComment = <<<EOF
    /**
     * Returns the "$name" polymorphic reference.
     *
     * @return Mandango\Document\Document|null The reference or null if it does not exist.
     */
EOF;
            }

            // setter
            $method = new Method('public', 'set'.ucfirst($name), '$value', $setCode);
            $method->setDocComment($setDocComment);
            $this->definitions['document_base']->addMethod($method);

            // getter
            $method = new Method('public', 'get'.ucfirst($name), '', $getCode);
            $method->setDocComment($getDocComment);
            $this->definitions['document_base']->addMethod($method);
        }
    }

    private function documentReferencesManyProcess()
    {
        foreach ($this->configClass['referencesMany'] as $name => $reference) {
            if (!empty($reference['inherited'])) {
                continue;
            }

            $addReferenceCache = '';
            if (!$this->configClass['isEmbedded']) {
                $addReferenceCache = <<<EOF
            if (!\$this->isNew()) {
                \$this->addReferenceCache('$name');
            }
EOF;
            }

            // normal
            if (isset($reference['class'])) {
                $getCode = <<<EOF
        if (!isset(\$this->data['referencesMany']['$name'])) {
            $addReferenceCache
            \$this->data['referencesMany']['$name'] = new \Mandango\Group\ReferenceGroup('{$reference['class']}', \$this, '{$reference['field']}');
        }

        return \$this->data['referencesMany']['$name'];
EOF;
                $getDocComment = <<<EOF
    /**
     * Returns the "$name" reference.
     *
     * @return Mandango\Group\ReferenceGroup The reference.
     */
EOF;
            // polymorphic
            } else {
                $discriminatorField = $reference['discriminatorField'];
                $discriminatorMap = $reference['discriminatorMap'];

                if ($discriminatorMap) {
                    $discriminatorMap = \Mandango\Mondator\Dumper::exportArray($discriminatorMap, 16);
                } else {
                    $discriminatorMap = 'false';
                }

                $getCode = <<<EOF
        if (!isset(\$this->data['referencesMany']['$name'])) {
            \$this->data['referencesMany']['$name'] = new \Mandango\Group\PolymorphicReferenceGroup('$discriminatorField', \$this, '{$reference['field']}', $discriminatorMap);
        }

        return \$this->data['referencesMany']['$name'];
EOF;
                $getDocComment = <<<EOF
    /**
     * Returns the "$name" polymorphic reference.
     *
     * @return Mandango\Group\PolymorphicReferenceGroup The reference.
     */
EOF;
            }

            // getter
            $method = new Method('public', 'get'.ucfirst($name), '', $getCode);
            $method->setDocComment($getDocComment);
            $this->definitions['document_base']->addMethod($method);

            // add
            $getter = 'get'.ucfirst($name);
            $method = new Method('public', 'add'.ucfirst($name), '$documents', <<<EOF
        \$this->$getter()->add(\$documents);

        return \$this;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Adds documents to the "$name" reference many.
     *
     * @param mixed \$documents A document or an array or documents.
     *
     * @return {$this->class} The document (fluent interface).
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);

            // remove
            $getter = 'get'.ucfirst($name);
            $method = new Method('public', 'remove'.ucfirst($name), '$documents', <<<EOF
        \$this->$getter()->remove(\$documents);

        return \$this;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Removes documents to the "$name" reference many.
     *
     * @param mixed \$documents A document or an array or documents.
     *
     * @return {$this->class} The document (fluent interface).
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    private function documentUpdateReferenceFieldsMethodProcess()
    {
        // inheritance
        $inheritance = '';
        if ($this->configClass['inheritance']) {
            $inheritance = <<<EOF
        parent::updateReferenceFields();

EOF;
        }

        $referencesCode = array();
        // references one
        foreach ($this->configClass['referencesOne'] as $name => $reference) {
            if (!empty($reference['inherited'])) {
                continue;
            }

            $fieldSetter = 'set'.ucfirst($reference['field']);

            // normal
            if (isset($reference['class'])) {
                $referencesCode[] = <<<EOF
        if (isset(\$this->data['referencesOne']['$name']) && !isset(\$this->data['fields']['{$reference['field']}'])) {
            \$this->$fieldSetter(\$this->data['referencesOne']['$name']->getId());
        }
EOF;
            // polymorphic
            } else {
                $discriminatorField = $reference['discriminatorField'];
                $discriminatorMap = $reference['discriminatorMap'];

                // discriminator map
                if ($discriminatorMap) {
                    $discriminatorMapValues = \Mandango\Mondator\Dumper::exportArray($discriminatorMap, 20);
                    $discriminatorValue = <<<EOF
                    if (false === \$discriminatorValue = array_search(get_class(\$document), $discriminatorMapValues)) {
                        throw new \RuntimeException(sprintf('The class "%s" is not a possible reference in the reference "$name" of the class "{$this->class}".', get_class(\$value)));
                    }
EOF;
                } else {
                    $discriminatorValue = <<<EOF
                    \$discriminatorValue = get_class(\$document);
EOF;
                }

                $referencesCode[] = <<<EOF
        if (isset(\$this->data['referencesOne']['$name']) && !isset(\$this->data['fields']['{$reference['field']}'])) {
            \$document = \$this->data['referencesOne']['$name'];
$discriminatorValue
            \$this->$fieldSetter(array(
                '$discriminatorField' => \$discriminatorValue,
                'id' => \$document->getId(),
            ));
        }
EOF;
            }
        }
        // references many
        foreach ($this->configClass['referencesMany'] as $name => $reference) {
            if (!empty($reference['inherited'])) {
                continue;
            }

            $fieldSetter = 'set'.ucfirst($reference['field']);
            $fieldGetter = 'get'.ucfirst($reference['field']);

            // normal
            if (isset($reference['class'])) {
                $referencesCode[] = <<<EOF
        if (isset(\$this->data['referencesMany']['$name'])) {
            \$group = \$this->data['referencesMany']['$name'];
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
            // polymorphic
            } else {
                $discriminatorField = $reference['discriminatorField'];
                $discriminatorMap = $reference['discriminatorMap'];

                // discriminator map
                if ($discriminatorMap) {
                    $discriminatorMapValues = \Mandango\Mondator\Dumper::exportArray($discriminatorMap, 20);
                    $discriminatorMapValues = <<<EOF
                \$discriminatorMapValues = $discriminatorMapValues;
EOF;
                    $discriminatorValue = <<<EOF
                    if (false === \$discriminatorValue = array_search(get_class(\$document), \$discriminatorMapValues)) {
                        throw new \RuntimeException(sprintf('The class "%s" is not a possible reference in the reference "$name" of the class "{$this->class}".', get_class(\$value)));
                    }
EOF;
                } else {
                    $discriminatorMapValues = '';
                    $discriminatorValue = <<<EOF
                    \$discriminatorValue = get_class(\$document);
EOF;
                }

                $referencesCode[] = <<<EOF
        if (isset(\$this->data['referencesMany']['$name'])) {
            \$group = \$this->data['referencesMany']['$name'];
            \$add = \$group->getAdd();
            \$remove = \$group->getRemove();
            if (\$add || \$remove) {
$discriminatorMapValues
                \$ids = \$this->$fieldGetter();
                foreach (\$add as \$document) {
$discriminatorValue
                    \$ids[] = array(
                        '$discriminatorField' => \$discriminatorValue,
                        'id' => \$document->getId(),
                    );
                }
                foreach (\$remove as \$document) {
$discriminatorValue
                    if (false !== \$key = array_search(\$search = array(
                        '$discriminatorField' => \$discriminatorValue,
                        'id' => \$document->getId(),
                    ), \$ids)) {
                        unset(\$ids[\$key]);
                    }
                }
                \$this->$fieldSetter(\$ids ? array_values(\$ids) : null);
            }
        }
EOF;
            }
        }
        $referencesCode = implode("\n", $referencesCode);

        $embeddedsCode = array();
        // embeddeds one
        foreach ($this->configClass['embeddedsOne'] as $name => $embedded) {
            if (!empty($embedded['inherited'])) {
                continue;
            }

            if (!$this->configClasses[$embedded['class']]['_has_references']) {
                continue;
            }

            $embeddedsCode[] = <<<EOF
        if (isset(\$this->data['embeddedsOne']['$name'])) {
            \$this->data['embeddedsOne']['$name']->updateReferenceFields();
        }
EOF;
        }
        // embeddeds many
        foreach ($this->configClass['embeddedsMany'] as $name => $embedded) {
            if (!empty($embedded['inherited'])) {
                continue;
            }

            if (!$this->configClasses[$embedded['class']]['_has_references']) {
                continue;
            }

            $embeddedsCode[] = <<<EOF
        if (isset(\$this->data['embeddedsMany']['$name'])) {
            \$group = \$this->data['embeddedsMany']['$name'];
            foreach (\$group->getSaved() as \$document) {
                \$document->updateReferenceFields();
            }
        }
EOF;
        }
        $embeddedsCode = implode("\n", $embeddedsCode);

        $method = new Method('public', 'updateReferenceFields', '', <<<EOF
$inheritance
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

    private function documentSaveReferencesMethodProcess()
    {
        // references one
        $referencesOneCode = array();
        foreach ($this->configClass['referencesOne'] as $name => $reference) {
            if (!empty($reference['inherited'])) {
                continue;
            }

            if (!isset($reference['class'])) {
                continue;
            }

            $referencesOneCode[] = <<<EOF
        if (isset(\$this->data['referencesOne']['$name'])) {
            \$this->data['referencesOne']['$name']->save();
        }
EOF;
        }
        $referencesOneCode = implode("\n", $referencesOneCode);

        // references many
        $referencesManyCode = array();
        foreach ($this->configClass['referencesMany'] as $name => $reference) {
            if (!empty($reference['inherited'])) {
                continue;
            }

            if (!isset($reference['class'])) {
                continue;
            }

            $referencesManyCode[] = <<<EOF
        if (isset(\$this->data['referencesMany']['$name'])) {
            \$group = \$this->data['referencesMany']['$name'];
            \$documents = array();
            foreach (\$group->getAdd() as \$document) {
                \$documents[] = \$document;
            }
            if (\$group->isSavedInitialized()) {
                foreach (\$group->getSaved() as \$document) {
                    \$documents[] = \$document;
                }
            }
            if (\$documents) {
                \$this->getMandango()->getRepository('{$reference['class']}')->save(\$documents);
            }
        }
EOF;
        }
        $referencesManyCode = implode("\n", $referencesManyCode);

        // embeddeds one
        $embeddedsOneCode = array();
        foreach ($this->configClass['embeddedsOne'] as $name => $embedded) {
            if (!empty($embedded['inherited'])) {
                continue;
            }

            if (!$this->configClasses[$embedded['class']]['_has_references']) {
                continue;
            }
            $embeddedsOneCode[] = <<<EOF
        if (isset(\$this->data['embeddedsOne']['$name'])) {
            \$this->data['embeddedsOne']['$name']->saveReferences();
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

    private function documentEmbeddedsOneProcess()
    {
        foreach ($this->configClass['embeddedsOne'] as $name => $embedded) {
            if (!empty($embedded['inherited'])) {
                continue;
            }

            // setter
            $rootDocument = !$this->configClass['isEmbedded'] ? '$this' : '$this->getRootDocument()';

            $method = new Method('public', 'set'.ucfirst($name), '$value', <<<EOF
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
            \$originalValue = isset(\$this->data['embeddedsOne']['$name']) ? \$this->data['embeddedsOne']['$name'] : null;
            \Mandango\Archive::set(\$this, 'embedded_one.$name', \$originalValue);
        } elseif (\Mandango\Archive::get(\$this, 'embedded_one.$name') === \$value) {
            \Mandango\Archive::remove(\$this, 'embedded_one.$name');
        }

        \$this->data['embeddedsOne']['$name'] = \$value;

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
            if (!$this->configClass['isEmbedded']) {
                $queryCode = <<<EOF
            if (\$this->isNew()) {
                \$this->data['embeddedsOne']['$name'] = null;
            } elseif (!isset(\$this->data['embeddedsOne']) || !array_key_exists('$name', \$this->data['embeddedsOne'])) {
                \$exists = \$this->getRepository()->getCollection()->findOne(array('_id' => \$this->id, '$name' => array('\$exists' => 1)));
                if (\$exists) {
                    \$embedded = new \\{$embedded['class']}(\$this->getMandango());
                    \$embedded->setRootAndPath(\$this, '$name');
                    \$this->data['embeddedsOne']['$name'] = \$embedded;
                } else {
                    \$this->data['embeddedsOne']['$name'] = null;
                }
            }
EOF;
            } else {
                $queryCode = <<<EOF
            if (
                (!isset(\$this->data['embeddedsOne']) || !array_key_exists('$name', \$this->data['embeddedsOne']))
                &&
                (\$rap = \$this->getRootAndPath())
                &&
                !\$this->isEmbeddedOneChangedInParent()
                &&
                false === strpos(\$rap['path'], '._add')
            ) {
                \$collection = \$this->getMandango()->getRepository(get_class(\$rap['root']))->getCollection();
                \$field = \$rap['path'].'.$name';
                \$result = \$collection->findOne(array('_id' => \$rap['root']->getId(), \$field => array('\$exists' => 1)));
                if (\$result) {
                    \$embedded = new \\{$embedded['class']}(\$this->getMandango());
                    \$embedded->setRootAndPath(\$rap['root'], \$field);
                    \$this->data['embeddedsOne']['$name'] = \$embedded;
                }
            }
            if (!isset(\$this->data['embeddedsOne']['$name'])) {
                \$this->data['embeddedsOne']['$name'] = null;
            }
EOF;
            }
            $method = new Method('public', 'get'.ucfirst($name), '', <<<EOF
        if (!isset(\$this->data['embeddedsOne']['$name'])) {
$queryCode
        }

        return \$this->data['embeddedsOne']['$name'];
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

    private function documentEmbeddedsManyProcess()
    {
        foreach ($this->configClass['embeddedsMany'] as $name => $embedded) {
            if (!empty($embedded['inherited'])) {
                continue;
            }

            if (!$this->configClass['isEmbedded']) {
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

            $method = new Method('public', 'get'.ucfirst($name), '', <<<EOF
        if (!isset(\$this->data['embeddedsMany']['$name'])) {
            \$this->data['embeddedsMany']['$name'] = \$embedded = new \Mandango\Group\EmbeddedGroup('{$embedded['class']}');
$rootAndPath
        }

        return \$this->data['embeddedsMany']['$name'];
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

            // add
            $getter = 'get'.ucfirst($name);
            $method = new Method('public', 'add'.ucfirst($name), '$documents', <<<EOF
        \$this->$getter()->add(\$documents);

        return \$this;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Adds documents to the "$name" embeddeds many.
     *
     * @param mixed \$documents A document or an array or documents.
     *
     * @return {$this->class} The document (fluent interface).
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);

            // remove
            $getter = 'get'.ucfirst($name);
            $method = new Method('public', 'remove'.ucfirst($name), '$documents', <<<EOF
        \$this->$getter()->remove(\$documents);

        return \$this;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Removes documents to the "$name" embeddeds many.
     *
     * @param mixed \$documents A document or an array or documents.
     *
     * @return {$this->class} The document (fluent interface).
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    private function documentRelationsOneProcess()
    {
        foreach ($this->configClass['relationsOne'] as $name => $relation) {
            $method = new Method('public', 'get'.ucfirst($name), '', <<<EOF
        return \$this->getMandango()->getRepository('{$relation['class']}')->createQuery(array('{$relation['reference']}' => \$this->getId()))->one();
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

    private function documentRelationsManyOneProcess()
    {
        foreach ($this->configClass['relationsManyOne'] as $name => $relation) {
            $method = new Method('public', 'get'.ucfirst($name), '', <<<EOF
        return \$this->getMandango()->getRepository('{$relation['class']}')->createQuery(array('{$relation['reference']}' => \$this->getId()));
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

    private function documentRelationsManyManyProcess()
    {
        foreach ($this->configClass['relationsManyMany'] as $name => $relation) {
            $method = new Method('public', 'get'.ucfirst($name), '', <<<EOF
        return \$this->getMandango()->getRepository('{$relation['class']}')->createQuery(array('{$relation['reference']}' => \$this->getId()));
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

    private function documentRelationsManyThroughProcess()
    {
        foreach ($this->configClass['relationsManyThrough'] as $name => $relation) {
            $method = new Method('public', 'get'.ucfirst($name), '', <<<EOF
        \$ids = array();
        foreach (\$this->getMandango()->getRepository('{$relation['through']}')->getCollection()
            ->find(array('{$relation['local']}' => \$this->getId()), array('{$relation['foreign']}' => 1))
        as \$value) {
            \$ids[] = \$value['{$relation['foreign']}'];
        }

        return \$this->getMandango()->getRepository('{$relation['class']}')->createQuery(array('_id' => array('\$in' => \$ids)));
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

    private function documentResetGroupsProcess()
    {
        $resetGroupsCode = array();

        // referencesMany
        foreach ($this->configClass['referencesMany'] as $name => $referenceMany) {
            $resetGroupsCode[] = <<<EOF
        if (isset(\$this->data['referencesMany']['$name'])) {
            \$this->data['referencesMany']['$name']->reset();
        }
EOF;
        }

        // embeddedsOne
        foreach ($this->configClass['embeddedsOne'] as $name => $embeddedMany) {
            if (!$this->configClasses[$embeddedMany['class']]['_has_groups']) {
                continue;
            }

            $resetGroupsCode[] = <<<EOF
    if (isset(\$this->data['embeddedsOne']['$name'])) {
        \$this->data['embeddedsOne']['$name']->resetGroups();
    }
EOF;
        }

        // embeddedsMany
        foreach ($this->configClass['embeddedsMany'] as $name => $embeddedMany) {
            if ($this->configClasses[$embeddedMany['class']]['_has_groups']) {
                $resetGroupsCode[] = <<<EOF
        if (isset(\$this->data['embeddedsMany']['$name'])) {
            \$group = \$this->data['embeddedsMany']['$name'];
            foreach (array_merge(\$group->getAdd(), \$group->getRemove()) as \$document) {
                \$document->resetGroups();
            }
            if (\$group->isSavedInitialized()) {
                foreach (\$group->getSaved() as \$document) {
                    \$document->resetGroups();
                }
            }
            \$group->reset();
        }
EOF;
            } else {
                $resetGroupsCode[] = <<<EOF
        if (isset(\$this->data['embeddedsMany']['$name'])) {
            \$this->data['embeddedsMany']['$name']->reset();
        }
EOF;
            }
        }

        $resetGroupsCode = implode("\n", $resetGroupsCode);

        $method = new Method('public', 'resetGroups', '', <<<EOF
$resetGroupsCode
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Resets the groups of the document.
     */
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    private function documentSetMethodProcess()
    {
        // inheritance
        $inheritance = '';
        if ($this->configClass['inheritance']) {
            $inheritance = <<<EOF
        try {
            return parent::set(\$name, \$value);
        } catch (\InvalidArgumentException \$e) {
        }
EOF;
        }

        // data
        $setCode = array();

        foreach (array_merge(
            $this->configClass['fields'],
            $this->configClass['referencesOne'],
            $this->configClass['embeddedsOne']
        ) as $name => $data) {
            if (!empty($data['inherited'])) {
                continue;
            }

            $setter = 'set'.ucfirst($name);

            $setCode[] = <<<EOF
        if ('$name' == \$name) {
            return \$this->$setter(\$value);
        }
EOF;
        }
        $setCode = implode("\n", $setCode);

        // method
        $method = new Method('public', 'set', '$name, $value', <<<EOF
$inheritance

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

    private function documentGetMethodProcess()
    {
        // inheritance
        $inheritance = '';
        if ($this->configClass['inheritance']) {
            $inheritance = <<<EOF
        try {
            return parent::get(\$name);
        } catch (\InvalidArgumentException \$e) {
        }
EOF;
        }

        // data
        $getCode = array();
        foreach (array_merge(
            $this->configClass['fields'],
            $this->configClass['referencesOne'],
            $this->configClass['referencesMany'],
            $this->configClass['embeddedsOne'],
            $this->configClass['embeddedsMany']
        ) as $name => $data) {
            if (!empty($data['inherited'])) {
                continue;
            }

            $getter = 'get'.ucfirst($name);

            $getCode[] = <<<EOF
        if ('$name' === \$name) {
            return \$this->$getter();
        }
EOF;
        }
        $getCode = implode("\n", $getCode);

        // method
        $method = new Method('public', 'get', '$name', <<<EOF
$inheritance

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

    private function documentFromArrayMethodProcess()
    {
        // inheritance
        $inheritance = '';
        if ($this->configClass['inheritance']) {
            $inheritance = <<<EOF
        parent::fromArray(\$array);
EOF;
        }

        // fields
        $fieldsCode = array();
        foreach ($this->configClass['fields'] as $name => $field) {
            if (!empty($field['inherited'])) {
                continue;
            }

            $setter = 'set'.ucfirst($name);
            $fieldsCode[] = <<<EOF
        if (isset(\$array['$name'])) {
            \$this->$setter(\$array['$name']);
        }
EOF;
        }
        $fieldsCode = "\n".implode("\n", $fieldsCode)."\n";

        // embeddeds one
        $embeddedsOneCode = array();
        foreach ($this->configClass['embeddedsOne'] as $name => $embedded) {
            if (!empty($embedded['inherited'])) {
                continue;
            }

            $setter = 'set'.ucfirst($name);
            $embeddedsOneCode[] = <<<EOF
        if (isset(\$array['$name'])) {
            \$embedded = new \\{$embedded['class']}(\$this->getMandango());
            \$embedded->fromArray(\$array['$name']);
            \$this->$setter(\$embedded);
        }
EOF;
        }
        $embeddedsOneCode = "\n".implode("\n", $embeddedsOneCode)."\n";

        // embeddeds many
        $embeddedsManyCode = array();
        foreach ($this->configClass['embeddedsMany'] as $name => $embedded) {
            if (!empty($embedded['inherited'])) {
                continue;
            }

            $getter = 'get'.ucfirst($name);
            $embeddedsManyCode[] = <<<EOF
        if (isset(\$array['$name'])) {
            \$embeddeds = array();
            foreach (\$array['$name'] as \$documentData) {
                \$embeddeds[] = \$embedded = new \\{$embedded['class']}(\$this->getMandango());
                \$embedded->setDocumentData(\$documentData);
            }
            \$this->$getter()->replace(\$embeddeds);
        }
EOF;
        }
        $embeddedsManyCode = "\n".implode("\n", $embeddedsManyCode)."\n";

        $method = new Method('public', 'fromArray', 'array $array', <<<EOF
$inheritance

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

    private function documentToArrayMethodProcess()
    {
        // inheritance
        $inheritance = <<<EOF
        \$array = array();
EOF;
        if ($this->configClass['inheritance']) {
            $inheritance = <<<EOF
        \$array = parent::toArray();
EOF;
        }

        // fields
        $referenceFields = array();
        foreach (array_merge($this->configClass['referencesOne'], $this->configClass['referencesMany']) as $reference) {
            $referenceFields[] = $reference['field'];
        }

        $fieldsCode = array();
        foreach ($this->configClass['fields'] as $name => $field) {
            if (!empty($field['inherited'])) {
                continue;
            }

            if (in_array($name, $referenceFields)) {
            $fieldsCode[] = <<<EOF
        if (\$withReferenceFields) {
            \$array['$name'] = \$this->get('$name');
        }
EOF;
            } else {
            $fieldsCode[] = <<<EOF
        \$array['$name'] = \$this->get('$name');
EOF;
            }
        }
        $fieldsCode = "\n".implode("\n", $fieldsCode)."\n";

        // method
        $method = new Method('public', 'toArray', '$withReferenceFields = false', <<<EOF
$inheritance
$fieldsCode
        return \$array;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Export the document data to an array.
     *
     * @param Boolean \$withReferenceFields Whether include the fields of references or not (false by default).
     *
     * @return array An array with the document data.
     */
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    private function documentEventsMethodsProcess()
    {
        foreach ($this->configClass['events'] as $event => $methods) {
            if (!$methods) {
                continue;
            }

            $eventMethodName = $event.'Event';
            $eventMethodCode = '';

            // methods
            $methodsCode = array();
            if ($this->configClass['_parent_events'][$event]) {
                $methodsCode[] = <<<EOF
        parent::$eventMethodName();
EOF;
            }
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

    private function documentQueryForSaveMethodProcess()
    {
        // fields
        $fieldsCode = '';
        if ($this->configClass['fields']) {
            $fieldsInsertCode = array();
            $fieldsUpdateCode = array();
            foreach ($this->configClass['fields'] as $name => $field) {
                if (!empty($field['inherited'])) {
                    continue;
                }

                $typeCode = strtr(TypeContainer::get($field['type'])->toMongoInString(), array(
                    '%from%' => "\$this->data['fields']['$name']",
                ));

                // insert
                $insertTypeCode = str_replace('%to%', "\$query['{$field['dbName']}']", $typeCode);
                $fieldsInsertCode[] = <<<EOF
                if (isset(\$this->data['fields']['$name'])) {
                    $insertTypeCode
                }
EOF;

                // update
                if (!$this->configClass['isEmbedded']) {
                    $updateTypeCode = str_replace('%to%', "\$query['\$set']['{$field['dbName']}']", $typeCode);
                    $fieldUpdateSetCode = <<<EOF
                            $updateTypeCode
EOF;
                    $fieldUpdateUnsetCode = <<<EOF
                            \$query['\$unset']['{$field['dbName']}'] = 1;
EOF;
                } else {
                    $updateTypeCode = str_replace('%to%', "\$query['\$set'][\$documentPath.'.{$field['dbName']}']", $typeCode);
                    $fieldUpdateSetCode = <<<EOF
                            $updateTypeCode
EOF;
                    $fieldUpdateUnsetCode = <<<EOF
                            \$query['\$unset'][\$documentPath.'.{$field['dbName']}'] = 1;
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

            if ($this->configClass['isEmbedded']) {
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

        // embeddeds one
        $embeddedsOneCode = '';
        if ($this->configClass['embeddedsOne']) {
            $embeddedsOneCode = array();
            foreach ($this->configClass['embeddedsOne'] as $name => $embedded) {
                if (!empty($embedded['inherited'])) {
                    continue;
                }

                $embeddedsOneCode[] = <<<EOF
            \$originalValue = \$this->getOriginalEmbeddedOneValue('$name');
            if (isset(\$this->data['embeddedsOne']['$name'])) {
                \$resetValue = \$reset ? \$reset : (!\$isNew && \$this->data['embeddedsOne']['$name'] !== \$originalValue);
                \$query = \$this->data['embeddedsOne']['$name']->queryForSave(\$query, \$isNew, \$resetValue);
            } elseif (array_key_exists('$name', \$this->data['embeddedsOne'])) {
                if (\$originalValue) {
                    \$rap = \$originalValue->getRootAndPath();
                    \$query['\$unset'][\$rap['path']] = 1;
                }
            }
EOF;
            }

            $embeddedsOneCode = implode("\n", $embeddedsOneCode);
            $embeddedsOneCode = <<<EOF
        if (isset(\$this->data['embeddedsOne'])) {
$embeddedsOneCode
        }
EOF;
        }

        // embeddeds many
        $embeddedsManyCode = '';
        if ($this->configClass['embeddedsMany']) {
            $embeddedsManyInsertCode = array();
            $embeddedsManyUpdateCode = array();
            foreach ($this->configClass['embeddedsMany'] as $name => $embedded) {
                if (!empty($embedded['inherited'])) {
                    continue;
                }

                $embeddedsManyInsertCode[] = <<<EOF
                if (isset(\$this->data['embeddedsMany']['$name'])) {
                    foreach (\$this->data['embeddedsMany']['$name']->getAdd() as \$document) {
                        \$query = \$document->queryForSave(\$query, \$isNew);
                    }
                }
EOF;
                $embeddedsManyUpdateCode[] = <<<EOF
                if (isset(\$this->data['embeddedsMany']['$name'])) {
                    \$group = \$this->data['embeddedsMany']['$name'];
                    foreach (\$group->getSaved() as \$document) {
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
        if (isset(\$this->data['embeddedsMany'])) {
            if (\$isNew) {
$embeddedsManyInsertCode
            } else {
$embeddedsManyUpdateCode
            }
        }
EOF;
        }

        // document or embedded
        if (!$this->configClass['isEmbedded']) {
            $arguments = '';
            // single inheritance
            if ($this->configClass['inheritance'] && 'single' == $this->configClass['inheritance']['type']) {
                $field = $this->configClass['inheritance']['field'];
                $value = $this->configClass['inheritance']['value'];
                $codeHeader = <<<EOF
        \$isNew = \$this->isNew();
        \$query = \$isNew ? array_merge(array('$field' => '$value'), parent::queryForSave()) : parent::queryForSave();
        \$reset = false;
EOF;
            } else {
                $codeHeader = <<<EOF
        \$isNew = \$this->isNew();
        \$query = array();
        \$reset = false;
EOF;
            }
        } else {
            $arguments = '$query, $isNew, $reset = false';
            $codeHeader = <<<EOF
EOF;
        }

        $method = new Method('public', 'queryForSave', $arguments, <<<EOF
$codeHeader

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

    private function repositoryDocumentClassPropertyProcess()
    {
        $property = new Property('protected', 'documentClass', $this->class);
        $this->definitions['repository_base']->addProperty($property);
    }

    private function repositoryIsFilePropertyProcess()
    {
        $property = new Property('protected', 'isFile', $this->configClass['isFile']);
        $this->definitions['repository_base']->addProperty($property);
    }

    private function repositoryConnectionNamePropertyProcess()
    {
        $property = new Property('protected', 'connectionName', $this->configClass['connection']);
        $this->definitions['repository_base']->addProperty($property);
    }

    private function repositoryCollectionNamePropertyProcess()
    {
        $property = new Property('protected', 'collectionName', $this->configClass['collection']);
        $this->definitions['repository_base']->addProperty($property);
    }

    private function repositoryCountMethodProcess()
    {
        if (!$this->configClass['inheritance'] || 'single' != $this->configClass['inheritance']['type']) {
            return;
        }

        $field = $this->configClass['inheritance']['field'];
        $value = $this->configClass['inheritance']['value'];

        $method = new Method('public', 'count', 'array $query = array()', <<<EOF
        \$query = array_merge(\$query, array('$field' => '$value'));

        return parent::count(\$query);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * {@inheritdoc}
     */
EOF
        );
        $this->definitions['repository_base']->addMethod($method);
    }

    private function repositoryRemoveMethodProcess()
    {
        if (!$this->configClass['inheritance'] || 'single' != $this->configClass['inheritance']['type']) {
            return;
        }

        $field = $this->configClass['inheritance']['field'];
        $value = $this->configClass['inheritance']['value'];

        $method = new Method('public', 'remove', 'array $query = array(), array $options = array()', <<<EOF
        \$query = array_merge(\$query, array('$field' => '$value'));

        return parent::remove(\$query, \$options);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * {@inheritdoc}
     */
EOF
        );
        $this->definitions['repository_base']->addMethod($method);
    }

    private function repositorySaveMethodProcess()
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

        // groups
        $resetGroupsCode = '';
        if ($this->configClass['_has_groups']) {
            $resetGroupsCode = <<<EOF
            \$document->resetGroups();
EOF;
        }

        // events
        $preInsert = '';
        if ($this->configClass['events']['preInsert'] || $this->configClass['_parent_events']['preInsert']) {
            $preInsert = "\$document->preInsertEvent();\n                ";
        }

        $postInsert = '';
        if ($this->configClass['events']['postInsert'] || $this->configClass['_parent_events']['postInsert']) {
            $postInsert = "\n                    \$document->postInsertEvent();";
        }

        $preUpdate = '';
        if ($this->configClass['events']['preUpdate'] || $this->configClass['_parent_events']['postUpdate']) {
            $preUpdate = "\$document->preUpdateEvent();\n                ";
        }

        $postUpdate = '';
        if ($this->configClass['events']['postUpdate'] || $this->configClass['_parent_events']['postUpdate']) {
            $postUpdate = "\n                \$document->postUpdateEvent();";
        }

        // method
        $method = new Method('public', 'save', '$documents, array $batchInsertOptions = array(), array $updateOptions = array()', <<<EOF
        if (!is_array(\$documents)) {
            \$documents = array(\$documents);
        }

        \$identityMap =& \$this->getIdentityMap()->allByReference();
        \$collection = \$this->getCollection();

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
                \$collection->batchInsert(\$a, \$batchInsertOptions);

                foreach (\$a as \$oid => \$data) {
                    \$document = \$inserts[\$oid];

                    \$document->setId(\$data['_id']);
                    \$document->clearModified();
                    \$identityMap[\$data['_id']->__toString()] = \$document;$resetGroupsCode$postInsert
                }
            }
        }

        // updates
        foreach (\$updates as \$document) {
            if (\$query = \$document->queryForSave()) {
                $preUpdate\$collection->update(array('_id' => \$document->getId()), \$query, \$updateOptions);
                \$document->clearModified();$resetGroupsCode$postUpdate
            }
        }
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Save documents.
     *
     * @param mixed \$documents          A document or an array of documents.
     * @param array \$batchInsertOptions The options for the batch insert operation (optional).
     * @param array \$updateOptions      The options for the update operation (optional).
     */
EOF
        );
        $this->definitions['repository_base']->addMethod($method);
    }

    private function repositoryDeleteMethodProcess()
    {
        // events
        $preDelete = '';
        if ($this->configClass['events']['preDelete'] || $this->configClass['_parent_events']['preDelete']) {
            $preDelete = "\$document->preDeleteEvent();\n                ";
        }

        $postDelete = '';
        if ($this->configClass['events']['postDelete'] || $this->configClass['_parent_events']['postDelete']) {
            $postDelete = <<<EOF


        foreach (\$documents as \$document) {
            \$document->postDeleteEvent();
        }
EOF;
        }

        // methods
        $method = new Method('public', 'delete', '$documents, array $removeOptions = array()', <<<EOF
        if (!is_array(\$documents)) {
            \$documents = array(\$documents);
        }

        \$identityMap =& \$this->getIdentityMap()->allByReference();

        \$ids = array();
        foreach (\$documents as \$document) {
            $preDelete\$ids[] = \$id = \$document->getAndRemoveId();
            unset(\$identityMap[\$id->__toString()]);
        }

        \$this->getCollection()->remove(array('_id' => array('\$in' => \$ids)), \$removeOptions);$postDelete
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Delete documents.
     *
     * @param mixed \$documents     A document or an array of documents.
     * @param array \$removeOptions The options for the remove operation (optional).
     */
EOF
        );
        $this->definitions['repository_base']->addMethod($method);
    }

    private function repositoryEnsureIndexesMethodProcess()
    {
        $indexesCode = array();
        foreach ($this->configClass['_indexes'] as $name => $index) {
            $keys    = \Mandango\Mondator\Dumper::exportArray($index['keys'], 12);
            $options = \Mandango\Mondator\Dumper::exportArray(array_merge(isset($index['options']) ? $index['options'] : array(), array('safe' => true)), 12);

            $indexesCode[] = <<<EOF
        \$this->getCollection()->ensureIndex($keys, $options);
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

    private function queryAllMethodProcess()
    {
        $variables = <<<EOF
        \$repository = \$this->getRepository();
        \$mandango = \$repository->getMandango();
        \$documentClass = \$repository->getDocumentClass();
        \$identityMap =& \$repository->getIdentityMap()->allByReference();
        \$isFile = \$repository->isFile();
EOF;
        $queryFields = <<<EOF
EOF;
        $createObjects = <<<EOF
            if (isset(\$identityMap[\$id])) {
                \$document = \$identityMap[\$id];
                \$document->addQueryHash(\$this->getHash());
            } else {
                if (\$isFile) {
                    \$file = \$data;
                    \$data = \$file->file;
                    \$data['file'] = \$file;
                }
                \$data['_query_hash'] = \$this->getHash();
                \$data['_fields'] = \$fields;

                \$document = new \$documentClass(\$mandango);
                \$document->setDocumentData(\$data);

                \$identityMap[\$id] = \$document;
            }
            \$documents[\$id] = \$document;
EOF;

        // single inheritance in inheritable
        if ($this->configClass['inheritable'] && 'single' == $this->configClass['inheritable']['type']) {
            $field = $this->configClass['inheritable']['field'];

            $variables = <<<EOF
        \$identityMaps = array();
        \$mandango = \$this->getRepository()->getMandango();
        \$isFile = \$this->getRepository()->isFile();
EOF;

            $queryFields = <<<EOF
        if (\$fields = \$this->getFields()) {
            \$fields['$field'] = 1;
            \$this->fields(\$fields);
        }
EOF;

            $createObjectsValues = array();
            foreach ($this->configClass['inheritable']['values'] as $value => $valueClass) {
                $createObjectsValues[] = <<<EOF
                if ('$value' == \$data['$field']) {
                    if (!isset(\$identityMaps['$value'])) {
                        \$identityMaps['$value'] = \$mandango->getRepository('{$valueClass}')->getIdentityMap()->allByReference();
                    }
                    \$documentClass = '$valueClass';
                    \$identityMap = \$identityMaps['$value'];
                }
EOF;
            }
            $createObjectsValues = implode("\n", $createObjectsValues);
            $createObjects = <<<EOF
            \$documentClass = null;
            \$identityMap = null;
            if (isset(\$data['$field'])) {
$createObjectsValues
            }
            if (null === \$documentClass) {
                if (!isset(\$identityMaps['_root'])) {
                    \$identityMaps['_root'] = \$this->getRepository()->getIdentityMap()->allByReference();
                }
                \$documentClass = '{$this->class}';
                \$identityMap = \$identityMaps['_root'];
            }

$createObjects
EOF;
        }

        $method = new Method('public', 'all', '', <<<EOF
$variables

$queryFields

        \$fields = array();
        foreach (array_keys(\$this->getFields()) as \$field) {
            if (false === strpos(\$field, '.')) {
                \$fields[\$field] = 1;
                continue;
            }
            \$f =& \$fields;
            foreach (explode('.', \$field) as \$name) {
                if (!isset(\$f[\$name])) {
                    \$f[\$name] = array();
                }
                \$f =& \$f[\$name];
            }
            \$f = 1;
        }

        \$documents = array();
        foreach (\$this->createCursor() as \$id => \$data) {
$createObjects
        }

        if (\$references = \$this->getReferences()) {
            \$mandango = \$this->getRepository()->getMandango();
            \$metadata = \$mandango->getMetadataFactory()->getClass(\$this->getRepository()->getDocumentClass());
            foreach (\$references as \$referenceName) {
                // one
                if (isset(\$metadata['referencesOne'][\$referenceName])) {
                    \$reference = \$metadata['referencesOne'][\$referenceName];
                    \$field = \$reference['field'];

                    \$ids = array();
                    foreach (\$documents as \$document) {
                        if (\$id = \$document->get(\$field)) {
                            \$ids[] = \$id;
                        }
                    }
                    if (\$ids) {
                        \$mandango->getRepository(\$reference['class'])->findById(array_unique(\$ids));
                    }

                    continue;
                }

                // many
                if (isset(\$metadata['referencesMany'][\$referenceName])) {
                    \$reference = \$metadata['referencesMany'][\$referenceName];
                    \$field = \$reference['field'];

                    \$ids = array();
                    foreach (\$documents as \$document) {
                        if (\$id = \$document->get(\$field)) {
                            foreach (\$id as \$i) {
                                \$ids[] = \$i;
                            }
                        }
                    }
                    if (\$ids) {
                        \$mandango->getRepository(\$reference['class'])->findById(array_unique(\$ids));
                    }

                    continue;
                }

                // invalid
                throw new \RuntimeException(sprintf('The reference "%s" does not exist in the class "%s".', \$referenceName, \$documentClass));
            }
        }

        return \$documents;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * {@inheritdoc}
     */
EOF
        );
        $this->definitions['query_base']->addMethod($method);
    }

    private function queryCreateCursorMethodProcess()
    {
        if (!$this->configClass['inheritance']) {
            return;
        }

        $field = $this->configClass['inheritance']['field'];
        $value = $this->configClass['inheritance']['value'];

        $method = new Method('public', 'createCursor', '', <<<EOF
        \$criteria = \$savedCriteria = \$this->getCriteria();
        \$criteria['$field'] = '$value';
        \$this->criteria(\$criteria);

        \$cursor = parent::createCursor();

        \$this->criteria(\$savedCriteria);

        return \$cursor;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * {@inheritdoc}
     */
EOF
        );
        $this->definitions['query_base']->addMethod($method);
    }

    /*
     * preGlobal
     */
    private function globalInheritableAndInheritanceProcess()
    {
        // inheritable
        foreach ($this->configClasses as $class => &$configClass) {
            if ($configClass['inheritable']) {
                if (!is_array($configClass['inheritable'])) {
                    throw new \RuntimeException(sprintf('The inheritable configuration of the class "%s" is not an array.', $class));
                }

                if (!isset($configClass['inheritable']['type'])) {
                    throw new \RuntimeException(sprintf('The inheritable configuration of the class "%s" does not have type.', $class));
                }

                if (!in_array($configClass['inheritable']['type'], array('single'))) {
                    throw new \RuntimeException(sprintf('The inheritable type "%s" of the class "%s" is not valid.', $configClass['inheritable']['type'], $class));
                }

                if ('single' == $configClass['inheritable']['type']) {
                    if (!isset($configClass['inheritable']['field'])) {
                        $configClass['inheritable']['field'] = 'type';
                    }
                    $configClass['inheritable']['values'] = array();
                }
            }
        }

        // inheritance
        foreach ($this->configClasses as $class => &$configClass) {
            if (!$configClass['inheritance']) {
                $configClass['_parent_events'] = array(
                    'preInsert'  => array(),
                    'postInsert' => array(),
                    'preUpdate'  => array(),
                    'postUpdate' => array(),
                    'preDelete'  => array(),
                    'postDelete' => array(),
                );
                continue;
            }

            if (!isset($configClass['inheritance']['class'])) {
                throw new \RuntimeException(sprintf('The inheritable configuration of the class "%s" does not have class.', $class));
            }
            $inheritanceClass = $configClass['inheritance']['class'];

            // inherited
            $inheritedFields = $this->configClasses[$inheritanceClass]['fields'];
            $inheritedReferencesOne = $this->configClasses[$inheritanceClass]['referencesOne'];
            $inheritedReferencesMany = $this->configClasses[$inheritanceClass]['referencesMany'];
            $inheritedEmbeddedsOne = $this->configClasses[$inheritanceClass]['embeddedsOne'];
            $inheritedEmbeddedsMany = $this->configClasses[$inheritanceClass]['embeddedsMany'];

            // inheritable
            if ($this->configClasses[$inheritanceClass]['inheritable']) {
                $inheritableClass = $inheritanceClass;
                $inheritable = $this->configClasses[$inheritanceClass]['inheritable'];
            } elseif ($this->configClasses[$inheritanceClass]['inheritance']) {
                $parentInheritance = $this->configClasses[$inheritanceClass]['inheritance'];
                do {
                    $continueSearchingInheritable = false;

                    // inherited
                    $inheritedFields = array_merge($inheritedFields, $this->configClasses[$parentInheritance['class']]['fields']);
                    $inheritedReferencesOne = array_merge($inheritedReferencesOne, $this->configClasses[$parentInheritance['class']]['referencesOne']);
                    $inheritedReferencesMany = array_merge($inheritanceReferencesMany, $this->configClasses[$parentInheritance['class']]['referencesMany']);
                    $inheritedEmbeddedsOne = array_merge($inheritedEmbeddedsOne, $this->configClasses[$parentInheritance['class']]['embeddedsOne']);
                    $inheritedEmbeddedsMany = array_merge($inheritedEmbeddedsMany, $this->configClasses[$parentInheritance['class']]['embeddedsMany']);

                    if ($this->configClasses[$parentInheritance['class']]['inheritable']) {
                        $inheritableClass = $parentInheritance['class'];
                        $inheritable = $this->configClasses[$parentInheritance['class']]['inheritable'];
                    } else {
                        $continueSearchingInheritance = true;
                        $parentInheritance = $this->configClasses[$parentInheritance['class']]['inheritance'];
                    }
                } while ($continueSearchingInheritable);
            } else {
                throw new \RuntimeException(sprintf('The class "%s" is not inheritable or has inheritance.', $configClass['inheritance']['class']));
            }

            // inherited fields
            foreach ($inheritedFields as $name => &$field) {
                if (is_string($field)) {
                    $field = array('type' => $field);
                }

                $field['inherited'] = true;
            }
            unset($field);
            $configClass['fields'] = array_merge($inheritedFields, $configClass['fields']);

            // inherited referencesOne
            foreach ($inheritedReferencesOne as $name => &$referenceOne) {
                $referenceOne['inherited'] = true;
            }
            unset($referenceOne);
            $configClass['referencesOne'] = array_merge($inheritedReferencesOne, $configClass['referencesOne']);

            $configClass['inheritance']['type'] = $inheritable['type'];

            // inherited referencesMany
            foreach ($inheritedReferencesMany as $name => &$referenceMany) {
                $referenceMany['inherited'] = true;
            }
            unset($referenceMany);
            $configClass['referencesMany'] = array_merge($inheritedReferencesMany, $configClass['referencesMany']);

            // inherited embeddedsOne
            foreach ($inheritedEmbeddedsOne as $name => &$embeddedOne) {
                $embeddedOne['inherited'] = true;
            }
            unset($embeddedOne);
            $configClass['embeddedsOne'] = array_merge($inheritedEmbeddedsOne, $configClass['embeddedsOne']);

            // inherited embeddedsMany
            foreach ($inheritedEmbeddedsMany as $name => &$embeddedMany) {
                $embeddedMany['inherited'] = true;
            }
            unset($embeddedMany);
            $configClass['embeddedsMany'] = array_merge($inheritedEmbeddedsMany, $configClass['embeddedsMany']);

            // parent events
            $parentEvents = array(
                'preInsert'  => array(),
                'postInsert' => array(),
                'preUpdate'  => array(),
                'postUpdate' => array(),
                'preDelete'  => array(),
                'postDelete' => array(),
            );
            $loopClass = $inheritableClass;
            do {
                $parentEvents = array_merge($parentEvents, $this->configClasses[$loopClass]['events']);
                if ($this->configClasses[$loopClass]['inheritable']) {
                    $continue = false;
                } else {
                    $continue = true;
                    $loopClass = $this->configClass[$loopClass]['inheritance']['class'];
                }
            } while ($continue);
            $configClass['_parent_events'] = $parentEvents;

            // type
            if ('single' == $inheritable['type']) {
                //single inheritance does not work with multiple inheritance
                if (!$this->configClasses[$configClass['inheritance']['class']]['inheritable']) {
                    throw new \RuntimeException(sprintf('The single inheritance does not work with multiple inheritance (%s).', $class));
                }

                if (!isset($configClass['inheritance']['value'])) {
                    throw new \RuntimeException(sprintf('The inheritable configuration in the class "%s" does not have value.', $class));
                }
                $value = $configClass['inheritance']['value'];
                if (isset($this->configClasses[$inheritableClass]['inheritable']['values'][$value])) {
                    throw new \RuntimeException(sprintf('The value "%s" is in the single inheritance of the class "%s" more than once.', $value, $inheritanceClass));
                }
                $this->configClasses[$inheritableClass]['inheritable']['values'][$value] = $class;

                $configClass['collection'] = $this->configClasses[$inheritableClass]['collection'];
                $configClass['inheritance']['field'] = $inheritable['field'];
            }
        }
    }

    private function globalHasReferencesProcess()
    {
        do {
             $continue = false;
             foreach ($this->configClasses as $class => $configClass) {
                 if (isset($configClass['_has_references'])) {
                     continue;
                 }

                 $hasReferences = false;
                 if ($configClass['referencesOne'] || $configClass['referencesMany']) {
                     $hasReferences = true;
                 }
                 foreach (array_merge($configClass['embeddedsOne'], $configClass['embeddedsMany']) as $name => $embedded) {
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

    private function globalHasGroupsProcess()
    {
        do {
            $continue = false;
            foreach ($this->configClasses as $class => $configClass) {
                if (isset($configClass['_has_groups'])) {
                    continue;
                }

                $hasGroups = false;
                if ($configClass['referencesMany'] || $configClass['embeddedsMany']) {
                    $hasGroups = true;
                }
                foreach (array_merge($configClass['embeddedsOne'], $configClass['embeddedsMany']) as $name => $embedded) {
                    if (!isset($this->configClasses[$embedded['class']]['_has_groups'])) {
                        $continue = true;
                        continue 2;
                    }
                    if ($this->configClasses[$embedded['class']]['_has_groups']) {
                        $hasGroups = true;
                    }
                }
                $configClass['_has_groups'] = $hasGroups;
            }
        } while($continue);
    }

    private function globalIndexesProcess()
    {
        do {
            $continue = false;
            foreach ($this->configClasses as $class => $configClass) {
                if (isset($configClass['_indexes'])) {
                    continue;
                }

                $indexes = $configClass['indexes'];
                foreach (array_merge($configClass['embeddedsOne'], $configClass['embeddedsMany']) as $name => $embedded) {
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
    private function globalMetadataProcess()
    {
        $output = new Output($this->getOption('metadata_factory_output'), true);
        $definition = new Definition($this->getOption('metadata_factory_class'), $output);
        $definition->setParentClass('\Mandango\MetadataFactory');
        $this->definitions['metadata_factory'] = $definition;

        $output = new Output($this->getOption('metadata_factory_output'), true);
        $definition = new Definition($this->getOption('metadata_factory_class').'Info', $output);
        $this->definitions['metadata_factory_info'] = $definition;

        $classes = array();
        foreach ($this->configClasses as $class => $configClass) {
            $classes[$class] = $configClass['isEmbedded'];

            $info = array();
            // general
            $info['isEmbedded'] = $configClass['isEmbedded'];
            if (!$info['isEmbedded']) {
                $info['mandango'] = $configClass['mandango'];
                $info['connection'] = $configClass['connection'];
                $info['collection'] = $configClass['collection'];
            }
            // inheritable
            $info['inheritable'] = $configClass['inheritable'];
            // inheritance
            $info['inheritance'] = $configClass['inheritance'];
            // fields
            $info['fields'] = $configClass['fields'];
            // references
            $info['_has_references'] = $configClass['_has_references'];
            $info['referencesOne'] = $configClass['referencesOne'];
            $info['referencesMany'] = $configClass['referencesMany'];
            // embeddeds
            $info['embeddedsOne'] = $configClass['embeddedsOne'];
            $info['embeddedsMany'] = $configClass['embeddedsMany'];
            // relations
            if (!$info['isEmbedded']) {
                $info['relationsOne'] = $configClass['relationsOne'];
                $info['relationsManyOne'] = $configClass['relationsManyOne'];
                $info['relationsManyMany'] = $configClass['relationsManyMany'];
                $info['relationsManyThrough'] = $configClass['relationsManyThrough'];
            }
            // indexes
            $info['indexes'] = $configClass['indexes'];
            $info['_indexes'] = $configClass['_indexes'];

            $info = \Mandango\Mondator\Dumper::exportArray($info, 12);

            $method = new Method('public', 'get'.str_replace('\\', '', $class).'Class', '', <<<EOF
        return $info;
EOF
            );
            $this->definitions['metadata_factory_info']->addMethod($method);
        }

        $property = new Property('protected', 'classes', $classes);
        $this->definitions['metadata_factory']->addProperty($property);
    }

    private function parseAndCheckAssociationClass(&$association, $name)
    {
        if (!is_array($association)) {
            throw new \RuntimeException(sprintf('The association "%s" of the class "%s" is not an array or string.', $name, $this->class));
        }

        if (!empty($association['class'])) {
            if (!is_string($association['class'])) {
                throw new \RuntimeException(sprintf('The class of the association "%s" of the class "%s" is not an string.', $name, $this->class));
            }
        } elseif (!empty($association['polymorphic'])) {
            if (empty($association['discriminatorField'])) {
                $association['discriminatorField'] = '_mandangoDocumentClass';
            }
            if (empty($association['discriminatorMap'])) {
                $association['discriminatorMap'] = false;
            }
        } else {
            throw new \RuntimeException(sprintf('The association "%s" of the class "%s" does not have class and it is not polymorphic.', $name, $this->class));
        }
    }
}
