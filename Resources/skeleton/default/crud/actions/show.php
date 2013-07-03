
    /**
     * Finds and displays a {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/show", name="{{ route_name_prefix }}_show")
     * @Template()
{% endif %}
     */
    public function showAction($id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_{{ entity|upper }}_SHOW')) {
            throw new AccessDeniedException();
        }
        $entity = $this->getRepository('{{ bundle }}:{{ entity }}')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity }} entity.');
        }
{% if 'annotation' == format %}
        return array(
            'entity' => $entity,
        );
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}CRUD:show.html.twig', array(
            'entity' => $entity,
        ));
{% endif %}
    }
