
    /**
     * Finds and displays a {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/show", name="{{ route_name_prefix }}_show")
     * @Template()
     * @Secure(roles="ROLE_{{ entity|upper }}_SHOW")
{% endif %}
     */
    public function showAction($id)
    {
        $entity = $this->getRepository('{{ bundle }}:{{ entity }}')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity }} entity.');
        }
{% if 'annotation' == format %}
        return array(
            'entity' => $entity,
        );
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:show.html.twig', array(
            'entity' => $entity,
        ));
{% endif %}
    }
