<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Alejandro Pérez Cuba <aprezcuba24@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CULabs\AdminBundle\Theme;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class ThemeDoctrineCrudCollection implements ThemeDoctrineCrudCollectionInterface
{
    protected $container;
    protected $themes_name;
    
    public function __construct(ContainerInterface $container, array $themes_name)
    {
        $this->container   = $container;
        $this->themes_name = $themes_name;
    }

    public function getTheme($name)
    {
        if (!isset ($this->themes_name[$name]))
            throw new InvalidArgumentException('Theme name not exist');
        
        $theme_service = $this->container->get($this->themes_name[$name]);
        
        if (!$theme_service instanceof ThemeDoctrineCrudInterface) {
            throw new InvalidArgumentException($theme_service, 'CULabs\AdminBundle\Theme\ThemeDoctrineCrudInterface');
        }
        
        return $theme_service;
    }
}