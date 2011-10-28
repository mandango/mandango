<?php

{% for event, methods in config_class.events %}
{% if methods|length %}
    /**
     * INTERNAL. Invoke the "{{ event }}" event.
     */
    public function {{ event }}Event()
    {
{# parent events #}
{% for method in config_class._parent_events[event] %}
        parent::{{ method }}();
{% endfor %}
{# methods #}
{% for method in methods %}
        $this->{{ method }}();
{% endfor %}
    }
{% endif %}
{% endfor %}
