<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Behat\Event;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\EntityManagerInterface;

class BehatCreateEvent extends Event
{
    /** @var  string */
    protected $type;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param string                 $type
     * @param array                  $data
     * @param EntityManagerInterface $entityManager
     */
    public function __construct($type, array $data, EntityManagerInterface $entityManager)
    {
        $this->setType($type);
        $this->setData($data);
        $this->setEntityManager($entityManager);
    }

    /**
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getDataItem($key, $default = null)
    {
        return isset($this->getData()[$key])? $this->getData()[$key]: $default;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setDataItemIfNotHas($key, $value)
    {
        if ($this->getDataItem($key)) {
            return;
        }

        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setDataItem($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type string
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data array
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }
}
