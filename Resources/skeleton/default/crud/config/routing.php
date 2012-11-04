<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

{% if 'index' in actions %}
$collection->add('{{ route_name_prefix }}', new Route('/', array(
    '_controller' => '{{ bundle }}:{{ entity }}CRUD:index',
)));
{% endif %}

{% if 'show' in actions %}
$collection->add('{{ route_name_prefix }}_show', new Route('/{id}/show', array(
    '_controller' => '{{ bundle }}:{{ entity }}CRUD:show',
)));
{% endif %}

{% if 'new' in actions %}
$collection->add('{{ route_name_prefix }}_new', new Route('/new', array(
    '_controller' => '{{ bundle }}:{{ entity }}CRUD:new',
)));

$collection->add('{{ route_name_prefix }}_create', new Route(
    '/create',
    array('_controller' => '{{ bundle }}:{{ entity }}CRUD:create'),
    array('_method' => 'post')
));
{% endif %}

{% if 'edit' in actions %}
$collection->add('{{ route_name_prefix }}_edit', new Route('/{id}/edit', array(
    '_controller' => '{{ bundle }}:{{ entity }}CRUD:edit',
)));

$collection->add('{{ route_name_prefix }}_update', new Route(
    '/{id}/update',
    array('_controller' => '{{ bundle }}:{{ entity }}CRUD:update'),
    array('_method' => 'post')
));
{% endif %}

{% if 'delete' in actions %}
$collection->add('{{ route_name_prefix }}_delete', new Route(
    '/{id}/delete',
    array('_controller' => '{{ bundle }}:{{ entity }}CRUD:delete'),
    array('_method' => 'post')
));
{% endif %}

return $collection;
