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

        $transformations = self::getTransformationsAndCorrespondingListeners(... $this->bindings);
        $events = self::getEventsAndCorrespondingListeners($event, ... $transformations);

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

    public function register(Listener $listener, Modifier $transformation): callable
    {
        $binding = Binding::create($listener, $transformation);
        $this->bindings[] = $binding;
        return function () use ($listener, $transformation) {
            $this->unregister($listener, $transformation);
        };
    }

    public function unregister(Listener $listener, Modifier $condition = null): void
    {
        $this->bindings = array_filter(
            $this->bindings,
            static function (Binding $binding) use ($listener, $condition) {
                $delinquent = $binding->getListener();
                $delinquentCondition = $binding->getModifier();
                $bindingIsExpired = !($delinquent instanceof Listener);
                switch (true) {
                    case ($bindingIsExpired):
                    case ($listener === $delinquent && $condition === $delinquentCondition):
                    case ($listener === $delinquent && $condition === null):
                        return self::DROP;
                }
                return self::KEEP;
            }
        );
    }

    private static function getTransformationsAndCorrespondingListeners(Binding ...$bindings)
    {
        $transformations = [];

        foreach ($bindings as $binding) {
            assert($binding instanceof Binding);
            $listener = $binding->getListener();
            if (!($listener instanceof Listener)) {
                continue;
            }
            $transformation = $binding->getModifier();
            $transformations[spl_object_id($transformation)] =
                $transformations[spl_object_id($transformation)]
                ?? [
                    'transformation' => $transformation,
                    'listeners' => []
                ];
            $transformations[spl_object_id($transformation)]['listeners'][spl_object_id($listener)] = $listener;
        }

        return array_values($transformations);
    }

    private static function getEventsAndCorrespondingListeners(Event $event, ...$transformations)
    {
        $events = [];

        foreach ($transformations as ['listeners' => $listeners, 'transformation' => $transformation]) {
            assert($transformation instanceof Modifier);
            $transformedEvent = $transformation->transform($event);
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