<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Behat;

use CULabs\AdminBundle\Behat\Event\BehatCreateEvent;

interface MinkContextInterface
{
    /**
     * @param  string $class
     * @param  array  $data
     * @param  bool   $flush
     * @return mixed
     */
    public function createEntity($class, $data, $flush = true);

    /**
     * @param BehatCreateEvent $event
     */
    public function createEntityByEvent(BehatCreateEvent $event);
}
