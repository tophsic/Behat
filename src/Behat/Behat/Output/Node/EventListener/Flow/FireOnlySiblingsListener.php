<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\Flow;

use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Behat fire only siblings listener.
 *
 * This listener catches all events, but proxies them to further listeners only if they
 * live inside specific event lifecycle (between BEFORE and AFTER events).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FireOnlySiblingsListener implements EventListener
{
    /**
     * @var \Behat\Testwork\Output\Node\EventListener\EventListener
     */
    private $descendant;
    /**
     * @var string
     */
    private $contextEventClass;
    /**
     * @var Boolean
     */
    private $inContext = false;

    /**
     * Initializes listener.
     *
     * @param string        $contextEventClass
     * @param \Behat\Testwork\Output\Node\EventListener\EventListener $descendant
     */
    public function __construct($contextEventClass, EventListener $descendant)
    {
        $this->descendant = $descendant;
        $this->contextEventClass = $contextEventClass;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if (!$this->isSubclassOfContextEventClass($event) && !$this->inContext) {
            return;
        }

        if ($this->isSubclassOfContextEventClass($event) && $event::BEFORE === $eventName) {
            $this->inContext = true;
        }

        if ($this->isSubclassOfContextEventClass($event) && $event::AFTER === $eventName) {
            $this->inContext = false;
        }

        $this->descendant->listenEvent($formatter, $event, $eventName);
    }

    /**
     * Checks if provided event is a subclass of context event class.
     *
     * @param Event $event
     *
     * @return Boolean
     */
    private function isSubclassOfContextEventClass(Event $event)
    {
        return $this->contextEventClass === get_class($event) || is_subclass_of($event, $this->contextEventClass);
    }
}