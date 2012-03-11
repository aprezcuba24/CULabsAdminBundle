
    /**
     * Deletes a {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/delete", name="{{ route_name_prefix }}_delete")
{% endif %}
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('{{ bundle }}:{{ entity }}')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity }} entity.');
        }
        $em->remove($entity);
        $em->flush();
        $this->getRequest()->getSession()->setFlash('notice', 'The entity {{ entity }} is deleted.');
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }