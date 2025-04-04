<?php

/*
 * This file is part of the eluceo/iCal package.
 *
 * (c) 2024 Markus Poerschke <markus@poerschke.nrw>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Eluceo\iCal\Domain\Collection;

use ArrayIterator;
use Eluceo\iCal\Domain\Entity\Event;
use Iterator;

final class EventsArray extends Events
{
    /**
     * @var array<int, Event>
     */
    private array $events = [];

    /**
     * @param array<array-key, Event> $events
     */
    public function __construct(array $events)
    {
        array_walk($events, [$this, 'addEvent']);
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->events);
    }

    public function addEvent(Event $event): void
    {
        $this->events[] = $event;
    }
}
