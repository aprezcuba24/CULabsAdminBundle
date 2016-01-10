<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class EntityEvent extends Event
{
    protected $entity;

    /**
     * @param $name String
     * @param $entity Object
     */
    public function __construct($name, $entity)
    {
        $this->setName($name);
        $this->entity = $entity;
    }

    /**
     * @return Object
     */
    public function getEntity()
    {
        return $this->entity;
    }
} 