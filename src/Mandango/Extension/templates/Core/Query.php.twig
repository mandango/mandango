<?php

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $repository = $this->getRepository();
        $mandango = $repository->getMandango();
        $documentClass = $repository->getDocumentClass();
        $identityMap =& $repository->getIdentityMap()->allByReference();
        $isFile = $repository->isFile();
{% if config_class.inheritable and 'single' == config_class.inheritable.type %}
        $identityMaps = array();
        $mandango = $this->getRepository()->getMandango();
        $isFile = $this->getRepository()->isFile();

        if ($fields = $this->getFields()) {
            $fields['{{ config_class.inheritable.field }}'] = 1;
            $this->fields($fields);
        }
{% endif %}

        $fields = array();
        foreach (array_keys($this->getFields()) as $field) {
            if (false === strpos($field, '.')) {
                $fields[$field] = 1;
                continue;
            }
            $f =& $fields;
            foreach (explode('.', $field) as $name) {
                if (!isset($f[$name])) {
                    $f[$name] = array();
                }
                $f =& $f[$name];
            }
            $f = 1;
        }

        $documents = array();
        foreach ($this->createCursor() as $id => $data) {
{% if config_class.inheritable and 'single' == config_class.inheritable.type %}
            $documentClass = null;
            $identityMap = null;
            if (isset($data['{{ config_class.inheritable.field }}'])) {
{% for value, valueClass in config_class.inheritable.values %}
                if ('{{ value }}' == $data['{{ config_class.inheritable.field }}']) {
                    if (!isset($identityMaps['{{ value }}'])) {
                        $identityMaps['{{ value }}'] = $mandango->getRepository('{{ valueClass }}')->getIdentityMap()->allByReference();
                    }
                    $documentClass = '{{ valueClass }}';
                    $identityMap = $identityMaps['{{ value }}'];
                }
{% endfor %}
            }
            if (null === $documentClass) {
                if (!isset($identityMaps['_root'])) {
                    $identityMaps['_root'] = $this->getRepository()->getIdentityMap()->allByReference();
                }
                $documentClass = '{{ class }}';
                $identityMap = $identityMaps['_root'];
            }
{% endif %}
            if (isset($identityMap[$id])) {
                $document = $identityMap[$id];
                $document->addQueryHash($this->getHash());
            } else {
                if ($isFile) {
                    $file = $data;
                    $data = $file->file;
                    $data['file'] = $file;
                }
                $data['_query_hash'] = $this->getHash();
                $data['_fields'] = $fields;

                $document = new $documentClass($mandango);
                $document->setDocumentData($data);

                $identityMap[$id] = $document;
            }
            $documents[$id] = $document;
        }

        if ($references = $this->getReferences()) {
            $mandango = $this->getRepository()->getMandango();
            $metadata = $mandango->getMetadataFactory()->getClass($this->getRepository()->getDocumentClass());
            foreach ($references as $referenceName) {
                // one
                if (isset($metadata['referencesOne'][$referenceName])) {
                    $reference = $metadata['referencesOne'][$referenceName];
                    $field = $reference['field'];

                    $ids = array();
                    foreach ($documents as $document) {
                        if ($id = $document->get($field)) {
                            $ids[] = $id;
                        }
                    }
                    if ($ids) {
                        $mandango->getRepository($reference['class'])->findById(array_unique($ids));
                    }

                    continue;
                }

                // many
                if (isset($metadata['referencesMany'][$referenceName])) {
                    $reference = $metadata['referencesMany'][$referenceName];
                    $field = $reference['field'];

                    $ids = array();
                    foreach ($documents as $document) {
                        if ($id = $document->get($field)) {
                            foreach ($id as $i) {
                                $ids[] = $i;
                            }
                        }
                    }
                    if ($ids) {
                        $mandango->getRepository($reference['class'])->findById(array_unique($ids));
                    }

                    continue;
                }

                // invalid
                throw new \RuntimeException(sprintf('The reference "%s" does not exist in the class "%s".', $referenceName, $documentClass));
            }
        }

        return $documents;
    }

{% if config_class.inheritance and 'single' == config_class.inheritance.type %}
    /**
     * {@inheritdoc}
     */
    public function createCursor()
    {
        $criteria = $savedCriteria = $this->getCriteria();
{% if config_class.inheritable %}
        $types = $this->getRepository()->getInheritableTypes();
        $types[] = '{{ config_class.inheritance.value }}';
        $criteria = array_merge($criteria, array('{{ config_class.inheritance.field }}' => array('$in' => $types)));
{% else %}
        $criteria = array_merge($criteria, array('{{ config_class.inheritance.field }}' => '{{ config_class.inheritance.value }}'));
{% endif %}
        $this->criteria($criteria);

        $cursor = parent::createCursor();

        $this->criteria($savedCriteria);

        return $cursor;
    }
{% endif %}
