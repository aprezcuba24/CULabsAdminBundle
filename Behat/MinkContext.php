<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Behat;

use Behat\MinkExtension\Context\MinkContext as BaseMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use CULabs\AdminBundle\Behat\Event\BehatCreateEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;

class MinkContext extends BaseMinkContext implements KernelAwareInterface, MinkContextInterface
{
    protected $kernel;
    protected $event_dispatcher;

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function setEventDispatcher(EventDispatcher $event_dispatcher)
    {
        $this->event_dispatcher = $event_dispatcher;
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
        $entityManager = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

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
        $this->pressButton('Login');
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
     * @Given /^There are the following "([^"]*)":$/
     */
    public function thereAreTheFollowing($class, TableNode $table)
    {
        /**@var $em \Doctrine\ORM\EntityManager*/
        $em = $this->getEntityManager();

        foreach ($table->getHash() as $row) {

            $event = new BehatCreateEvent($this, $class, $row, $em, false);
            $this->getEventDispatcher()->dispatch('behat.create_entity', $event);
            if (!$event->isProcessed()) {
                $this->createEntity($class, $row, false);
            }
        }

        $em->flush();
    }

    /**
     * @param BehatCreateEvent $event
     */
    public function createEntityByEvent(BehatCreateEvent $event)
    {
        $this->createEntity($event->getType(), $event->getData(), $event->getFlush());
    }

    /**
     * @param string $class
     * @param array $data
     * @param bool $flush
     * @return mixed
     */
    public function createEntity($class, $data, $flush = true)
    {
        /**@var $em \Doctrine\ORM\EntityManager*/
        $em = $this->getEntityManager();
        $entity = new $class();
        foreach ($data as $field => $value) {

            if (is_string($value)) {
                $value = explode(',', $value);
            }

            $add_method = sprintf('add%s', ucfirst($field));
            $set_method = sprintf('set%s', ucfirst($field));

            if (method_exists($entity, $add_method)) {
                foreach ($value as $item) {
                    $entity->$add_method($item);
                }
            } else {

                if (is_array($value)) {
                    $value = $value[0];
                }
                $entity->$set_method($value);
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
        return $this->getService('router')->generate($route, $parameters, $absolute);
    }

    /**
     * Get service by id.
     *
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * Returns Container instance.
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
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
} 