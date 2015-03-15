<?php
/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Twig;

use CULabs\AdminBundle\Menu\MenuConfigBuilder;

class TwigMenuExtension extends \Twig_Extension
{
    protected $menuConfigBuilder;

    public function __construct(MenuConfigBuilder $menuConfigBuilder)
    {
        $this->menuConfigBuilder = $menuConfigBuilder;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('set_route_active', [$this, 'setRouteActive']),
        );
    }

    public function setRouteActive($route_active)
    {
        $this->menuConfigBuilder->setRouteActive($route_active);
    }

    public function getName()
    {
        return 'menu_extension';
    }
} 