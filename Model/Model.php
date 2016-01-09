<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Model;

use CULabs\AdminBundle\Event\EntityEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class Model
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    protected $entity_manager;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container        = $container;
        $this->entity_manager   = $this->get('doctrine.orm.entity_manager');
        $this->event_dispatcher = $this->get('event_dispatcher');
    }

    protected function get($id)
    {
        return $this->container->get($id);
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

    public function save($entity, $flush = true)
    {
        if ($entity->getId()) {
            $this->update($entity, $flush);
        } else {
            $this->create($entity, $flush);
        }
    }

    /**
     * @param $entity Object
     * @param bool $flush
     */
    public function create($entity, $flush = true)
    {
        $this->throwEvent('pre_create_'.$this->getType($entity), $entity);
        $this->entity_manager->persist($entity);

        if ($flush) {
            $this->entity_manager->flush();
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
            $this->entity_manager->flush();
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
            $this->entity_manager->flush();
            $this->throwEvent('post_remove_'.$this->getType($entity), $entity);
        }
    }

    public function __call($name, $arguments)
    {
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