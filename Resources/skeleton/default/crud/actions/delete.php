
    /**
     * Deletes a {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/delete", name="{{ route_name_prefix }}_delete")
     * @Secure(roles="ROLE_{{ entity|upper }}_DELETE")
{% endif %}
     */
    public function deleteAction($id)
    {
        $entity = $this->getRepository('{{ bundle }}:{{ entity }}')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity }} entity.');
        }
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
        $this->setFlash('notice', 'The entity {{ entity }} is deleted.');
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }
    