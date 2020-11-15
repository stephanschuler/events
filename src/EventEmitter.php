<?php
declare(strict_types=1);

namespace StephanSchuler\Events;

use StephanSchuler\Events\Modification\Modifier;
use function assert;
use function spl_object_id;

class EventEmitter
{
    private const DROP = false;
    private const KEEP = true;

    protected $bindings = [];

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function dispatch(Event $event): void
    {

        $modifiers = self::getModifiersAndCorrespondingListeners(... $this->bindings);
        $events = self::getEventsAndCorrespondingListeners($event, ... $modifiers);

        foreach ($events as ['listeners' => $listeners, 'event' => $transformedEvent]) {
            assert($transformedEvent instanceof Event);
            foreach ($listeners as $listener) {
                assert($listener instanceof Listener);
                $listener->consumeEvent($transformedEvent);
            }
        }
    }

    public function getEvents(): Events
    {
        return Events::create($this);
    }

    public function register(Listener $listener, Modifier $modifier): callable
    {
        $binding = Binding::create($listener, $modifier);
        $this->bindings[] = $binding;
        return function () use ($listener, $modifier) {
            $this->unregister($listener, $modifier);
        };
    }

    public function unregister(Listener $listener, Modifier $modifier = null): void
    {
        $this->bindings = array_filter(
            $this->bindings,
            static function (Binding $binding) use ($listener, $modifier) {
                $delinquent = $binding->getListener();
                $delinquentModifier = $binding->getModifier();
                $bindingIsExpired = !($delinquent instanceof Listener);
                switch (true) {
                    case ($bindingIsExpired):
                    case ($listener === $delinquent && $modifier === $delinquentModifier):
                    case ($listener === $delinquent && $modifier === null):
                        return self::DROP;
                }
                return self::KEEP;
            }
        );
    }

    private static function getModifiersAndCorrespondingListeners(Binding ...$bindings)
    {
        $modifiers = [];

        foreach ($bindings as $binding) {
            assert($binding instanceof Binding);
            $listener = $binding->getListener();
            if (!($listener instanceof Listener)) {
                continue;
            }
            $modifier = $binding->getModifier();
            $modifiers[spl_object_id($modifier)] =
                $modifiers[spl_object_id($modifier)]
                ?? [
                    'modifier' => $modifier,
                    'listeners' => []
                ];
            $modifiers[spl_object_id($modifier)]['listeners'][spl_object_id($listener)] = $listener;
        }

        return array_values($modifiers);
    }

    private static function getEventsAndCorrespondingListeners(Event $event, ...$modifiers)
    {
        $events = [];

        foreach ($modifiers as ['listeners' => $listeners, 'modifier' => $modifier]) {
            assert($modifier instanceof Modifier);
            $transformedEvent = $modifier->transform($event);
            if (!$transformedEvent) {
                continue;
            }
            $events[spl_object_id($transformedEvent)] =
                $events[spl_object_id($transformedEvent)]
                ?? [
                    'event' => $transformedEvent,
                    'listeners' => []
                ];
            $events[spl_object_id($transformedEvent)]['listeners'] += $listeners;
        }

        return array_values($events);
    }
}