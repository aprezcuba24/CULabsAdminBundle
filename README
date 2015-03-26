CULabsAdminBundle
==================

Resumen
-------
Este bundle es con el objetivo de crear el módulo de administración, fundamentalmente para la creación de los casos de uso CRUD.
Este bundle depende de los bundles: 
LexikFormFilterBundle: https://github.com/lexik/LexikFormFilterBundle
KnpPaginatorBundle: http://github.com/KnpLabs/KnpPaginatorBundle
KnpMenuBundle: https://github.com/KnpLabs/KnpMenuBundle

Instlación
----------
```json
{
    "require": {
        "culabs/admin-bundle": "2.6.*@dev"
    }
}
```
Actulizar los vendors
```
php /home/util/composer.phar update --prefer-dist
```
Adicionar los bundles en ``AppKernel``
```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new CULabs\AdminBundle\CULabsAdminBundle(),
        new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
        new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
        new Knp\Bundle\MenuBundle\KnpMenuBundle(),
        // ...
    );
}
```
Configuración
-------------
Usar ``bootstrap_3_horizontal_layout.html.twig`` como tema de los formularios en ``config.yml``
```yaml
twig:
    form:
        resources:
          - bootstrap_3_horizontal_layout.html.twig
```
Crear el fichero ``menu.yml`` para la configuración del menú del backend
```yaml
parameters:
  menu.backend:
    items:
```
Importar el fichero ``menu.yml`` en ``config.yml``
```yaml
imports:
    - { resource: menu.yml }
```
Configurar ``cu_labs_admin`` en ``config.yml``
```yaml
cu_labs_admin:
    menu_backend: menu.backend
```
Crear la plantilla base para el backend con el nombre ``backend_base.html.twig``
```jinja
{% extends 'CULabsAdminBundle:Layout:base.html.twig' %}
{% block title %}CULabs Admin{% endblock %}
{% block title_app_url(path('admin_dashboard')) %}
{% block url_logout('#') %}
```
Crear la ruta ``admin_dashboard`` que debe apuntar al dasboard, inicialmente puede hacer un redirect a cualquier otra ruta
```yaml
admin_dashboard:
    path: /admin
    defaults:
        _controller: FrameworkBundle:Redirect:redirect
        route: admin_task
```
Publicar los assets
```
php app/console assets:install --symlink
```
Los ejemplos se desarrollarón según los siguientes entidades:
```
AppBundle\Entity\Task
AppBundle\Entity\Product\Product
```
Generar el crud para las dos entidades:
```
php app/console culabs:generate:crud --entity=AppBundle:Task --route-prefix=/admin/task --with-write -n
php app/console culabs:generate:crud --entity=AppBundle:Product/Product --route-prefix=/admin/product --with-write -n
```
Configurar el menú para los dos casos de usos en ``menu.yml``
```yaml
parameters:
  menu.backend:
    items:
      dashboard:
        route: admin_dashboard
        icon:  fa-home
      task:
        route: admin_task
        icon:  fa-gear
      product:
        route: admin_product
        icon:  fa-gear
```
Configurar la seguridad para acceder a las acciones en ``security.yml``
```yaml
role_hierarchy:
    ROLE_ADMIN:
        - ROLE_TASK_LIST
        - ROLE_TASK_EDIT
        - ROLE_TASK_NEW
        - ROLE_TASK_SHOW
        - ROLE_TASK_DELETE

        - ROLE_PRODUCT_PRODUCT_LIST
        - ROLE_PRODUCT_PRODUCT_NEW
        - ROLE_PRODUCT_PRODUCT_EDIT
        - ROLE_PRODUCT_PRODUCT_DELETE
        - ROLE_PRODUCT_PRODUCT_SHOW
access_control:
    - { path: ^/admin, roles: ROLE_ADMIN }
```  
Probar las rutas
```
http://127.0.0.1:8000/app_dev.php/admin/product
http://127.0.0.1:8000/app_dev.php/admin/task
```
Para configurar los formularios de los filtros debe editar los ficheros ``src/AppBundle/Filter/TaskFilterType.php`` y ``src/AppBundle/Filter/Product/ProductFilterType.php`` y hacerlo según la documentación de ``LexikFormFilterBundle``
