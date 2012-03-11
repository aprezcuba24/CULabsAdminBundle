    
    protected function deleteBatch($ids)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $qb = $em->getRepository('{{ bundle }}:{{ entity }}')->createQueryBuilder('{{ entity }}');
        $qb->delete()->where($qb->expr()->in('{{ entity }}.id', $ids));
        $qb->getQuery()->execute();
        
        $this->getRequest()->getSession()->setFlash('notice', 'The records are deleted.');
    }