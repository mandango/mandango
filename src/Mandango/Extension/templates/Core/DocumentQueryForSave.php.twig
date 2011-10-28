<?php

    /**
     * Query for save.
     */
    public function queryForSave({% if config_class.isEmbedded %}$query, $isNew, $reset = false{% endif %})
    {
{% if not config_class.isEmbedded %}
{% if config_class.inheritance and 'single' == config_class.inheritance.type %}
        $isNew = $this->isNew();
        $query = parent::queryForSave();
        if ($isNew) {
            $query['{{ config_class.inheritance.field }}'] = '{{ config_class.inheritance.value }}';
        }
        $reset = false;

{% else %}
        $isNew = $this->isNew();
        $query = array();
        $reset = false;

{% endif %}
{% endif %}
{# fields #}
{% if config_class.fields|length %}
        if (isset($this->data['fields'])) {
{# insert #}
            if ($isNew || $reset) {
{% if config_class.isEmbedded %}
                $rootQuery = $query;
                $query =& $rootQuery;
                $rap = $this->getRootAndPath();
                if (true === $reset) {
                    $path = array('$set', $rap['path']);
                } elseif ('deep' == $reset) {
                    $path = explode('.', '$set.'.$rap['path']);
                } else {
                    $path = explode('.', $rap['path']);
                }
                foreach ($path as $name) {
                    if (0 === strpos($name, '_add')) {
                        $name = substr($name, 4);
                    }
                    if (!isset($query[$name])) {
                        $query[$name] = array();
                    }
                    $query =& $query[$name];
                }
{% endif %}
{% for name, field in config_class.fields %}
{% if field.inherited is not defined or not field.inherited %}
                if (isset($this->data['fields']['{{ name }}'])) {
                    {{ mandango_type_to_mongo(field.type, "$this->data['fields']['" ~ name ~ "']", "$query['" ~ field.dbName ~ "']") }}
                }
{% endif %}
{% endfor %}
{% if config_class.isEmbedded %}
                unset($query);
                $query = $rootQuery;
{% endif %}
{# update #}
            } else {
{% if config_class.isEmbedded %}
                $rap = $this->getRootAndPath();
                $documentPath = $rap['path'];
{% endif %}
{% for name, field in config_class.fields %}
{% if field.inherited is not defined or not field.inherited %}
                if (isset($this->data['fields']['{{ name }}']) || array_key_exists('{{ name }}', $this->data['fields'])) {
                    $value = $this->data['fields']['{{ name }}'];
                    $originalValue = $this->getOriginalFieldValue('{{ name }}');
                    if ($value !== $originalValue) {
                        if (null !== $value) {
{% if not config_class.isEmbedded %}
                            {{ mandango_type_to_mongo(field.type, "$this->data['fields']['" ~ name ~ "']", "$query['$set']['" ~ field.dbName ~ "']") }}
{% else %}
                            {{ mandango_type_to_mongo(field.type, "$this->data['fields']['" ~ name ~ "']", "$query['$set'][$documentPath.'." ~ field.dbName ~ "']") }}
{% endif %}
                        } else {
{% if not config_class.isEmbedded %}
                            $query['$unset']['{{ field.dbName }}'] = 1;
{% else %}
                            $query['$unset'][$documentPath.'.{{ field.dbName }}'] = 1;
{% endif %}
                        }
                    }
                }
{% endif %}
{% endfor %}
            }
        }
{% endif %}
        if (true === $reset) {
            $reset = 'deep';
        }
{# embeddeds one #}
{% if config_class.embeddedsOne|length %}
        if (isset($this->data['embeddedsOne'])) {
{% for name, embedded in config_class.embeddedsOne %}
{% if embedded.inherited is not defined or not embedded.inherited %}
            $originalValue = $this->getOriginalEmbeddedOneValue('{{ name }}');
            if (isset($this->data['embeddedsOne']['{{ name }}'])) {
                $resetValue = $reset ? $reset : (!$isNew && $this->data['embeddedsOne']['{{ name }}'] !== $originalValue);
                $query = $this->data['embeddedsOne']['{{ name }}']->queryForSave($query, $isNew, $resetValue);
            } elseif (array_key_exists('{{ name }}', $this->data['embeddedsOne'])) {
                if ($originalValue) {
                    $rap = $originalValue->getRootAndPath();
                    $query['$unset'][$rap['path']] = 1;
                }
            }
{% endif %}
{% endfor %}
        }
{% endif %}
{# embeddeds many #}
{% if config_class.embeddedsMany|length %}
        if (isset($this->data['embeddedsMany'])) {
            if ($isNew) {
{% for name, embedded in config_class.embeddedsMany %}
{% if embedded.inherited is not defined or not embedded.inherited %}
                if (isset($this->data['embeddedsMany']['{{ name }}'])) {
                    foreach ($this->data['embeddedsMany']['{{ name }}']->getAdd() as $document) {
                        $query = $document->queryForSave($query, $isNew);
                    }
                }
{% endif %}
{% endfor %}
            } else {
{% for name, embedded in config_class.embeddedsMany %}
{% if embedded.inherited is not defined or not embedded.inherited %}
                if (isset($this->data['embeddedsMany']['{{ name }}'])) {
                    $group = $this->data['embeddedsMany']['{{ name }}'];
                    foreach ($group->getSaved() as $document) {
                        $query = $document->queryForSave($query, $isNew);
                    }
                    $groupRap = $group->getRootAndPath();
                    foreach ($group->getAdd() as $document) {
                        $q = $document->queryForSave(array(), true);
                        $rap = $document->getRootAndPath();
                        foreach (explode('.', $rap['path']) as $name) {
                            if (0 === strpos($name, '_add')) {
                                $name = substr($name, 4);
                            }
                            $q = $q[$name];
                        }
                        $query['$pushAll'][$groupRap['path']][] = $q;
                    }
                    foreach ($group->getRemove() as $document) {
                        $rap = $document->getRootAndPath();
                        $query['$unset'][$rap['path']] = 1;
                    }
                }
{% endif %}
{% endfor %}
            }
        }
{% endif %}

        return $query;
    }
