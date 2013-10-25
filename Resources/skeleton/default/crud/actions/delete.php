
    /**
     * Deletes a {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/delete", name="{{ route_name_prefix }}_delete")
{% endif %}
     */
    public function deleteAction($id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_{{ entity|upper }}_DELETE')) {
            throw new AccessDeniedException();
        }
        $entity = $this->getRepository('{{ bundle }}:{{ entity }}')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity }} entity.');
        }
        try {
            $em = $this->getManager();
            $em->remove($entity);
            $em->flush();
            $this->addFlash('notice', 'The entity is deleted.');

            return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
        } catch (\Exception $e) {
            $this->addFlash('error', 'The entity is not deleted.');
        }
    }
    