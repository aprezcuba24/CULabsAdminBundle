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

use CULabs\AdminBundle\Generator\DoctrineFiterGenerator;
use CULabs\AdminBundle\Generator\DoctrineFormGenerator;
use CULabs\AdminBundle\Generator\DoctrineModelGenerator;

interface ThemeDoctrineCrudInterface
{
    /**
     * @return DoctrineCrudGenerator
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getGenerator();

    /**
     * @return DoctrineFormGenerator
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getFormGenerator();

    /**
     * @return DoctrineFiterGenerator
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getFilterFormGenerator();

    /**
     * @return DoctrineModelGenerator
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getModelGenerator();
}
