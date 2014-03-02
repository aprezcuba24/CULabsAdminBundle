<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Renier Ricardo Figueredo <aprezcuba24@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CULabs\AdminBundle\Theme;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class ThemeDoctrineCrudCollection implements ThemeDoctrineCrudCollectionInterface
{
    protected $themes = array();

    public function __construct(array $themes)
    {
        $this->themes = $themes;
    }

    public function addTheme($name, $theme_service)
    {
        $this->themes[$name] = $theme_service;
    }

    public function getTheme($name)
    {
        if (!isset($this->themes[$name])) {

            throw new InvalidArgumentException('Theme name not exist');
        }

        $theme_service = $this->themes[$name];

        if (!$theme_service instanceof ThemeDoctrineCrudInterface) {

            throw new InvalidArgumentException($theme_service, 'CULabs\AdminBundle\Theme\ThemeDoctrineCrudInterface');
        }

        return $theme_service;
    }
}
