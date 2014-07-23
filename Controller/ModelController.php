<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Controller;

use CULabs\AdminBundle\Model\Model;

abstract class ModelController extends CRUDController
{
    protected abstract function getModel();

    protected function findOr404($entity, $criterias = array())
    {
        if (!($entity instanceof Model)) {
            return parent::findOr404($entity, $criterias);
        }
        $model = $entity;

        $findMethod = is_scalar($criterias) ? 'find' : 'findOneBy';

        $result = $model->$findMethod($criterias);
        if (!$result) {
            throw $this->createNotFoundException();
        }

        return $result;
    }

    /**
     * @param $entity Object
     * @param bool $flush
     */
    protected function persist($entity, $flush = true)
    {
        if ($entity->getId()) {
            $this->create($entity, $flush);
        } else {
            $this->update($entity, $flush);
        }
    }

    /**
     * @param $entity Object
     * @param bool $flush
     */
    protected function create($entity, $flush = true)
    {
        $this->getModel()->create($entity, $flush);
    }

    /**
     * @param $entity Object
     * @param bool $flush
     */
    protected function update($entity, $flush = true)
    {
        $this->getModel()->update($entity, $flush);
    }

    /**
     * @param $entity Object
     * @param bool $flush
     */
    protected function remove($entity, $flush = true)
    {
        $this->getModel()->remove($entity, $flush);
    }
} 
