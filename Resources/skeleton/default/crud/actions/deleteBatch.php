    
    protected function deleteBatch($ids)
    {
        $qb = $this->getRepository('{{ bundle }}:{{ entity }}')->createQueryBuilder('{{ entity }}');
        $qb->delete()->where($qb->expr()->in('{{ entity }}.id', $ids));
        $result = $qb->getQuery()->getResult();

        $em = $this->getDoctrine()->getManager();
        try {
            foreach ($result as $item) {
                $em->remove($item);
            }
            $em->flush();
            $this->addFlash('notice', 'The records are deleted.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'The records are not deleted.');
        }
    }
    