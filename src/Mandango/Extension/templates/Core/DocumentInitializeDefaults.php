<?php

    /**
     * Initializes the document defaults.
     */
    public function initializeDefaults()
    {
{% for name, field in config_class.fields %}
{% if field.default is defined %}
        $this->set{{ name|ucfirst }}({{ field.default|var_export }});
{% endif %}
{% endfor %}
    }
