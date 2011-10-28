<?php

    /**
     * Resets the groups of the document.
     */
    public function resetGroups()
    {
{# references many #}
{% for name, reference in config_class.referencesMany %}
        if (isset($this->data['referencesMany']['{{ name }}'])) {
            $this->data['referencesMany']['{{ name }}']->reset();
        }
{% endfor %}
{# embeddeds one #}
{% for name, embedded in config_class.embeddedsOne %}
{% if config_classes[embedded.class]['_has_groups'] %}
        if (isset($this->data['embeddedsOne']['{{ name }}'])) {
            $this->data['embeddedsOne']['{{ name }}']->resetGroups();
        }
{% endif %}
{% endfor %}
{# embeddeds many #}
{% for name, embedded in config_class.embeddedsMany %}
{% if config_classes[embedded.class]['_has_groups'] %}
        if (isset($this->data['embeddedsMany']['{{ name }}'])) {
            $group = $this->data['embeddedsMany']['{{ name }}'];
            foreach (array_merge($group->getAdd(), $group->getRemove()) as $document) {
                $document->resetGroups();
            }
            if ($group->isSavedInitialized()) {
                foreach ($group->getSaved() as $document) {
                    $document->resetGroups();
                }
            }
            $group->reset();
        }
{% else %}
        if (isset($this->data['embeddedsMany']['{{ name }}'])) {
            $this->data['embeddedsMany']['{{ name }}']->reset();
        }
{% endif %}
{% endfor %}
    }
