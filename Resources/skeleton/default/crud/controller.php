<?php

namespace {{ namespace }}\Controller{{ entity_namespace ? '\\' ~ entity_namespace : '' }};

use CULabs\AdminBundle\Controller\CRUDController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
{% if 'annotation' == format -%}
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
{%- endif %}

use {{ namespace }}\Entity\{{ entity }};
{% if 'new' in actions or 'edit' in actions %}
use {{ namespace }}\Form\{{ entity }}Type;
{% endif %}
{%- if 'index' in actions %}
use {{ namespace }}\Filter\{{ entity }}FilterType;
{% endif %}

/**
 * {{ entity }} controller.
 *
{% if 'annotation' == format %}
 * @Route("/{{ route_prefix }}")
{% endif %}
 */
class {{ entity_class }}CRUDController extends CRUDController
{

    {%- if 'index' in actions %}
        {%- include 'actions/index.php' %}
    {%- endif %}

    {%- if 'show' in actions %}
        {%- include 'actions/show.php' %}
    {%- endif %}

    {%- if 'new' in actions %}
        {%- include 'actions/new.php' %}
    {%- endif %}

    {%- if 'edit' in actions %}
        {%- include 'actions/edit.php' %}
    {%- endif %}

    {%- if 'delete' in actions %}
        {%- include 'actions/delete.php' %}
    {%- endif %}
    
    {%- if 'batch' in actions %}
        {%- include 'actions/batch.php' %}
    {%- endif %}
    
    {%- if 'batch' in actions and ('delete' in actions) %}
        {%- include 'actions/deleteBatch.php' %}
    {%- endif %}
    
    {%- include 'actions/utilmethods.php' %}
    
}
