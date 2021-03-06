<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Model;

use CULabs\AdminBundle\Event\EntityEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class Model
{
    protected $entity_manager;
    protected $event_dispatcher;

    /**
     * @param EntityManagerInterface $entity_manager
     */
    public function __construct(EntityManagerInterface $entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    /**
     * @param $entity String
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($entity)
    {
        return $this->entity_manager->getRepository($entity);
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected abstract function getCurrentRepository();

    /**
     * @return Object
     */
    public abstract function createEntity();

    /**
     * @param $entity Object
     * @param bool $flush
     */
    public function create($entity, $flush = true)
    {
        $this->throwEvent('pre_create_'.$this->getType($entity), $entity);
        $this->entity_manager->persist($entity);

        if ($flush) {
            $this->entity_manager->flush($entity);
            $this->throwEvent('post_create_'.$this->getType($entity), $entity);
        }
    }

    /**
     * @param $entity Object
     * @param bool $flush
     */
    public function update($entity, $flush = true)
    {
        $this->throwEvent('pre_update_'.$this->getType($entity), $entity);
        $this->entity_manager->persist($entity);

        if ($flush) {
            $this->entity_manager->flush($entity);
            $this->throwEvent('post_update_'.$this->getType($entity), $entity);
        }
    }

    /**
     * @param $entity Object
     * @param bool $flush
     */
    public function remove($entity, $flush = true)
    {
        $this->throwEvent('pre_remove_'.$this->getType($entity), $entity);
        $this->entity_manager->remove($entity);

        if ($flush) {
            $this->entity_manager->flush($entity);
            $this->throwEvent('post_remove_'.$this->getType($entity), $entity);
        }
    }

    public function __call($name, $arguments)
    {
        if (!method_exists($this->getCurrentRepository(), $name)) {
            throw new \BadFunctionCallException(sprintf('Method "%s" not exists', $name));
        }

        return call_user_func_array(array($this->getCurrentRepository(), $name), $arguments);
    }

    /**
     * @param $entity Object
     * @param String $name
     */
    protected function throwEvent($name, $entity)
    {
        if (!$this->getEventDispatcher()) {
            return;
        }

        $this->getEventDispatcher()->dispatch($name, new EntityEvent($name, $entity));
    }

    /**
     * @param $entity
     * @return mixed
     */
    protected function getType($entity)
    {
        return str_replace('//', '_', get_class($entity));
    }

    /**
     * @param EventDispatcherInterface $event_dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $event_dispatcher)
    {
        $this->event_dispatcher = $event_dispatcher;
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->event_dispatcher;
    }
} 