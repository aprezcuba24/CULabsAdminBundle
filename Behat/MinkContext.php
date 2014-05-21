<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Behat;

use Behat\MinkExtension\Context\MinkContext as BaseMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelDictionary;
use CULabs\AdminBundle\Behat\Event\BehatCreateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;

class MinkContext extends BaseMinkContext implements KernelAwareContext
{
    protected $event_dispatcher;
    protected $kernel;

    public function __construct(EventDispatcherInterface $event_dispatcher)
    {
        $this->event_dispatcher = $event_dispatcher;
    }

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->kernel->boot();
    }

    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    public function getEventDispatcher()
    {
        if (!$this->event_dispatcher) {
            $this->event_dispatcher = new EventDispatcher();
        }

        return $this->event_dispatcher;
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger($entityManager);
        $purger->purge();
    }

    /**
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($user)
    {
        $this->getSession()->visit($this->generateUrl('fos_user_security_login'));
        $this->fillField('_username', $user);
        $this->fillField('_password', $user);
        $this->pressButton('security.login.submit');
    }

    /**
     * @Then I logout
     */
    public function iLogout()
    {
        $this->getSession()->visit($this->generateUrl('fos_user_security_logout'));
    }

    protected function getUrlByEntityAndFieldValue($pattern, $entity_name, $field_value)
    {
        /**@var $em \Doctrine\ORM\EntityManager*/
        $em = $this->getEntityManager();
        $field_value = explode(':', $field_value);

        $entity = $em->getRepository($entity_name)->findOneBy(array(
            $field_value[0] => $field_value[1],
        ));

        if (!$entity) {
            throw new ExpectationException(sprintf('There are not entity (%s) with "%s" equals "%s"', $entity_name, $field_value[0], $field_value[1]), $this->getSession());
        }

        return preg_replace_callback('~{([a-z]*)}~', function ($mach) use ($entity) {
            return $entity->{'get'.ucfirst($mach[1])}();
        }, $pattern);
    }

    /**
     * @Then /^I should be on "([^"]*)" of "([^"]*)" entity "([^"]*)"$/
     */
    public function iShouldBeOnOfEntity($url, $field_value, $entity_name)
    {
        $this->assertPageAddress($this->getUrlByEntityAndFieldValue($url, $entity_name, $field_value));
    }

    /**
     * @When /^I am on "([^"]*)" of "([^"]*)" entity "([^"]*)"$/
     */
    public function iAmOnOfEntity($url, $field_value, $entity_name)
    {
        $this->visit($this->getUrlByEntityAndFieldValue($url, $entity_name, $field_value));
    }

    /**
     * @Given There are the following :class:
     */
    public function thereAreTheFollowing($class, TableNode $table)
    {
        /**@var $em \Doctrine\ORM\EntityManager*/
        $em = $this->getEntityManager();

        foreach ($table->getHash() as $row) {

            $event = new BehatCreateEvent($class, $row, $em, false);
            $this->getEventDispatcher()->dispatch('behat.create_entity.'.$class, $event);
            $this->createEntity($event->getType(), $event->getData(), false);
        }

        $em->flush();
    }

    /**
     * @param  string $class
     * @param  array  $data
     * @param  bool   $flush
     * @return mixed
     */
    public function createEntity($class, $data, $flush = true)
    {
        /**@var $em \Doctrine\ORM\EntityManager*/
        $em = $this->getEntityManager();
        $entity = new $class();
        foreach ($data as $field => $value_item) {

            if (is_string($value_item)) {
                $value = explode(',', $value_item);
            }

            $add_method = sprintf('add%s', ucfirst($field));
            $set_method = sprintf('set%s', ucfirst($field));

            if (method_exists($entity, $add_method)) {
                foreach ($value as $item) {
                    $entity->$add_method($item);
                }
            } else {
                $entity->$set_method($value_item);
            }
        }
        $em->persist($entity);
        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Generate url.
     *
     * @param string  $route
     * @param array   $parameters
     * @param Boolean $absolute
     *
     * @return string
     */
    protected function generateUrl($route, array $parameters = array(), $absolute = false)
    {
        return $this->get('router')->generate($route, $parameters, $absolute);
    }

    /**
     * Get service by id.
     *
     * @param string $id
     *
     * @return object
     */
    protected function get($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * Get entity manager.
     *
     * @return ObjectManager
     */
    public function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    public function getRepository($repository)
    {
        return $this->getEntityManager()->getRepository($repository);
    }
}