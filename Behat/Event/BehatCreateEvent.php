<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Behat\Event;

use CULabs\AdminBundle\Behat\MinkContextInterface;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\EntityManager;

class BehatCreateEvent extends Event
{
    /** @var bool */
    protected $isProcessed = false;

    /** @var  string */
    protected $type;

    /** @var bool */
    protected $flush = true;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var MinkContextInterface
     */
    protected $subject;

    /**
     * @param MinkContextInterface $subject
     * @param string               $type
     * @param array                $data
     * @param EntityManager        $entityManager
     * @param bool                 $flush
     */
    public function __construct(MinkContextInterface $subject, $type, array $data, EntityManager $entityManager, $flush = true)
    {
        $this->setType($type);
        $this->setData($data);
        $this->setFlush($flush);
        $this->setEntityManager($entityManager);
        $this->setSubject($subject);
    }

    /**
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getDataItem($key, $default)
    {
        return isset($this->getData()[$key])? $this->getData()[$key]: $default;
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
     * @return bool
     */
    public function isProcessed()
    {
        return $this->isProcessed;
    }

    /**
     * @param $isProcessed bool
     */
    public function setIsProcessed($isProcessed)
    {
        $this->isProcessed = $isProcessed;
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
     * @param $flush bool
     */
    public function setFlush($flush)
    {
        $this->flush = $flush;
    }

    /**
     * @return bool
     */
    public function getFlush()
    {
        return $this->flush;
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
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
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

    /**
     * @param $subject MinkContextInterface
     */
    public function setSubject(MinkContextInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return MinkContextInterface
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
