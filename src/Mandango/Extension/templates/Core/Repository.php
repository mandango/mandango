<?php

    /**
     * {@inheritdoc}
     */
    public function __construct(\Mandango\Mandango $mandango)
    {
        $this->documentClass = '{{ class }}';
        $this->isFile = {{ config_class.isFile ? 'true' : 'false' }};
{% if config_class.connection %}
        $this->connectionName = '{{ config_class.connection }}';
{% endif %}
        $this->collectionName = '{{ config_class.collection }}';

        parent::__construct($mandango);
    }

{% if config_class.inheritance and 'single' == config_class.inheritance.type %}
    /**
     * {@inheritdoc}
     */
    public function count(array $query = array())
    {
        $query = array_merge($query, array('{{ config_class.inheritance.field }}' => '{{ config_class.inheritance.value }}'));

        return parent::count($query);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $query = array())
    {
        $query = array_merge($query, array('{{ config_class.inheritance.field }}' => '{{ config_class.inheritance.value }}'));

        return parent::remove($query);
    }
{% endif %}

    /**
     * Save documents.
     *
     * @param mixed $documents A document or an array of documents.
     */
    public function save($documents)
    {
        if (!is_array($documents)) {
            $documents = array($documents);
        }

        $identityMap =& $this->getIdentityMap()->allByReference();
        $collection = $this->getCollection();

        $inserts = array();
        $updates = array();
        foreach ($documents as $document) {
{% if config_class._has_references %}
            $document->saveReferences();
            $document->updateReferenceFields();
{% endif %}
            if ($document->isNew()) {
                $inserts[spl_object_hash($document)] = $document;
            } else {
                $updates[] = $document;
            }
        }

        // insert
        if ($inserts) {
            $a = array();
            foreach ($inserts as $oid => $document) {
{% if config_class._has_references %}
                if (!$document->isModified()) {
                    continue;
                }
{% endif %}
{% if config_class.events.preInsert or config_class._parent_events.preInsert %}
                $document->preInsertEvent();
{% endif %}
                $a[$oid] = $document->queryForSave();
            }

            if ($a) {
                $collection->batchInsert($a);

                foreach ($a as $oid => $data) {
                    $document = $inserts[$oid];

                    $document->setId($data['_id']);
                    $document->clearModified();
                    $identityMap[$data['_id']->__toString()] = $document;
{% if config_class._has_groups %}
                    $document->resetGroups();
{% endif %}
{% if config_class.events.postInsert or config_class._parent_events.postInsert %}
                $document->postInsertEvent();
{% endif %}
                }
            }
        }

        // updates
        foreach ($updates as $document) {
            if ($query = $document->queryForSave()) {
{% if config_class.events.preUpdate or config_class._parent_events.preUpdate %}
                $document->preUpdateEvent();
{% endif %}
                $collection->update(array('_id' => $document->getId()), $query);
                $document->clearModified();
{% if config_class._has_groups %}
                $document->resetGroups();
{% endif %}
{% if config_class.events.postUpdate or config_class._parent_events.postUpdate %}
                $document->postUpdateEvent();
{% endif %}
            }
        }
    }

    /**
     * Delete documents.
     *
     * @param mixed $documents A document or an array of documents.
     */
    public function delete($documents)
    {
        if (!is_array($documents)) {
            $documents = array($documents);
        }

        $identityMap =& $this->getIdentityMap()->allByReference();

        $ids = array();
        foreach ($documents as $document) {
{% if config_class.events.preDelete or config_class._parent_events.preDelete %}
            $document->preDeleteEvent();
{% endif %}
            $ids[] = $id = $document->getAndRemoveId();
            unset($identityMap[$id->__toString()]);
        }

        $this->getCollection()->remove(array('_id' => array('$in' => $ids)));

{% if config_class.events.postDelete or config_class._parent_events.postDelete %}
        foreach ($documents as $document) {
            $document->postDeleteEvent();
        }
{% endif %}
    }

    /**
     * Ensure the inexes.
     */
    public function ensureIndexes()
    {
{% for index in config_class._indexes %}
         $this->getCollection()->ensureIndex({{ index.keys|var_export }}, {{ index.options is defined ? index.options|var_export : 'array()' }});
{% endfor %}
    }
