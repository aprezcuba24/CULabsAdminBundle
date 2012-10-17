
    /**
     * Batch actions for {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/batch", name="{{ route_name_prefix }}_batch")
     * @Secure(roles="ROLE_{{ entity|upper }}_DELETE")
{% endif %}
     */
    public function batchAction()
    {
        $method = $this->getRequest()->request->get('batch_action');
        if (!$method) {
            $this->setFlash('error', 'Select a action');
            return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
        }
        $method = $method.'Batch';
        
        if (!method_exists($this, $method)) {
            throw new \UnexpectedValueException('The bacth method not defined');
        }
        
        $ids = $this->getRequest()->request->get('ids');
        
        if (!count($ids)) {
            $this->setFlash('error', 'Select a record');
            return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
        }
        
        $this->$method($ids);
        
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }
    