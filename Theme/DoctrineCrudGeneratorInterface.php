<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Theme;

interface DoctrineCrudGeneratorInterface
{
    const TEST_ENVIRONMENT_BEHAT   = 'BEHAT';
    const TEST_ENVIRONMENT_PHPUNIT = 'PHPUNIT';

    public function testEnvironment($test_environment);
}
