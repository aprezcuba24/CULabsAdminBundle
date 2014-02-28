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

interface ThemeDoctrineCrudInterface
{
    public function getGenerator();
    public function getFormGenerator();
    public function getFilterFormGenerator();
}