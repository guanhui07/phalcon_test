test

<!--
https://olddocs.phalconphp.com/en/3.0.0/reference/volt.html
-->

<p>
    <?php echo $this->tag->submitButton("Register"); ?>
    {{ postId|trim }}{{'test'}}

</p>


{% if show_navigation %}
            <ul id="navigation">
                {% for key , item in menu %}
                    <li>
                        <a href="{{ item['href'] }}">
                            {{ item['caption'] }}
                        </a>
                    </li>
                {% endfor %}
            </ul>
        {% endif %}

        <h1>{{ post['title'] }}</h1>

        <div class="content">
            {{ post["content"] }}
        </div>
{% set test1 = 'test'|trim %}

{{test1}}
{% set decoded = '{"one":11,"two":2,"three":3}'|json_decode(1) %}
{{ decoded['one'] }}

{# note: this is a comment 注释
    {% set price = 100; %}
#}

{# 定义数组 #}
{% set numbers = ['one': 'one1', 'two': 'two', 'three': 3] %}

{% for name, value in numbers %}
    {% if value != 'two' %}
     {{ name }} => {{ value }} <br />
     {% elseif 1==1 %}
        <br /> <hr />
     {% endif %}
{% endfor %}

{{ numbers['two']}}

{% if 0 or 1 %}
<br />ok

{% endif %}


{% if 2 and 1 %}
<br />ok !

{% endif %}

{% if ( not 0) %}
<br />ok 0!

{% endif %}

{# 字符串拼接 #}
{{ "hello " ~ "world" }}

{# strpos #}

{% if 'a' in 'abc' %}
<br />ok strpos!

{% endif %}


{% set at1 = 'a' ?  "<br />"~'isset b'~"<br />" :'c' %}

{# isset #}
{% if ( at1 is defined) %}
{{at1}}
{%endif%}

{# is_string is_array is_object is_int #}
{% set external = false %}
{% if external is type('boolean') %}
    {{ "external is false or true" }}
{% endif %}

{# 数组长度count,字符串长度strlen #}
{{ at1|length }}

