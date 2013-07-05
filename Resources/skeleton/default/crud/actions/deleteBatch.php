    
    protected function deleteBatch($ids)
    {
        $qb = $this->getRepository('{{ bundle }}:{{ entity }}')->createQueryBuilder('{{ entity }}');
        $qb->delete()->where($qb->expr()->in('{{ entity }}.id', $ids));
        $qb->getQuery()->execute();
        
         $this->addFlash('notice', 'The records are deleted.');
    }
    