<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Behat;

use Behat\Mink\Exception\ResponseTextException;
use Behat\MinkExtension\Context\MinkContext as BaseMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\HttpKernel\KernelInterface;
use CULabs\AdminBundle\Behat\Event\BehatCreateEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;

class MinkContext extends BaseMinkContext implements KernelAwareContext
{
    protected $event_dispatcher;
    protected $kernel;

    public function __construct(EventDispatcher $event_dispatcher = null)
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
     * @Given /^I am logged in OAuth "([^"]*)"$/
     */
    public function loginOAuth($user)
    {
        $this->iAmOnHomepage();
        $this->iAmOnHomepage();
        $this->visit("/pagomio/login/check?code=code");
        $this->iAmOnHomepage();
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
     * @Given See the page :arg1
     */
    public function seeThePage($arg1)
    {
        echo substr($this->getSession()->getPage()->getContent(), 0, $arg1);
        exit;
    }

    /**
     * @Then I should see in content :text
     */
    public function iShouldSeeInContent($text)
    {
        $actual = $this->getSession()->getPage()->getContent();
        $actual = preg_replace('/\s+/u', ' ', $actual);
        $regex  = '/'.preg_quote($text, '/').'/ui';

        if (preg_match($regex, $actual)) {
            $message = sprintf('The text "%s" appears in the text of this page, but it should not.', $text);
            throw new ResponseTextException($message, $this->session);
        }
    }

    /**
     * @Given I fill in :select with :option entity :entity field :field
     */
    public function iFillInWithEntityField($select, $option, $entity, $field)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $entity = $this->getRepository($entity)->findOneBy([$field => $option]);

        return $this->selectOption($select, $entity->getId());
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
            } else {
                $value = $value_item;
            }

            $add_method = sprintf('add%s', ucfirst($field));
            $set_method = sprintf('set%s', ucfirst($field));

            if (method_exists($entity, $add_method) && is_array($value)) {
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

        return $entity;
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

    /**
     * @Given I should see :text in :class_css
     */
    public function iShouldSeeIn($text, $class_css)
    {
        $this->textMatch($class_css, $text, true);
    }

    /**
     * @Given I should not see :text in :class_css
     */
    public function iShouldNotSeeIn($text, $class_css)
    {
        $this->textMatch($class_css, $text, false);
    }

    protected function textMatch($class_css, $text, $exist)
    {
        $page = $this->getSession()->getPage();
        $node = $page->find('css', $class_css);
        $actual = $node->getText();

        $actual = preg_replace('/\s+/u', ' ', $actual);
        $regex  = '/'.preg_quote($text, '/').'/ui';

        if ($exist) {
            if (!preg_match($regex, $actual)) {
                $message = sprintf('The text "%s" was not found anywhere in the "%s".', $text, $class_css);
                throw new ResponseTextException($message, $this->getSession());
            }
        } else {
            if (preg_match($regex, $actual)) {
                $message = sprintf('The text "%s" was found in the "%s".', $text, $class_css);
                throw new ResponseTextException($message, $this->getSession());
            }
        }
    }

    public function getSymfonyProfile()
    {
        $driver = $this->getSession()->getDriver();
        $profile = $driver->getClient()->getProfile();
        if (false === $profile) {
            throw new \RuntimeException(
                'Emails cannot be tested as the profiler is '.
                'disabled.'
            );
        }

        return $profile;
    }

    /**
     * @Then I should get an email form :email_to
     */
    public function iShouldGetAnEmail($email_to)
    {
        $error     = sprintf('No message sent to "%s"', $email_to);
        $profile   = $this->getSymfonyProfile();
        $collector = $profile->getCollector('swiftmailer');

        foreach ($collector->getMessages() as $message) {
            $correctRecipient = array_key_exists(
                $email_to, $message->getTo()
            );

            if (count($correctRecipient)) {
                return;
            }
        }

        throw new ExpectationException($error, $this->getSession());
    }

    /**
     * @Given /^intersect redirection$/
     */
    public function theRedirectionsAreIntercepted()
    {
        $this->getSession()->getDriver()->getClient()->followRedirects(false);
    }

    /**
     * @When /^I follow the redirection$/
     * @Then /^I should be redirected$/
     */
    public function iFollowTheRedirection()
    {
        $client = $this->getSession()->getDriver()->getClient();
        $client->followRedirects(true);
        $client->followRedirect();
    }
}
