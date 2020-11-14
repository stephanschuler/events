Events
======

Do not use in production. This is just an experiment for now.

This is not PSR-14, nor does it aim to be. But maybe it will end up being more or less PSR-14 in the future.
For now this is just an experiment. I'm trying to discover stuff around events.


Emitting and consuming events
-----------------------------

The basic concept is just like in any other event system.

```php
use StephanSchuler\Events;

$emitter = Events\EventEmitter::create();

$events = $emitter->getEvents();
$events->register(new class implements Events\Listener {
    public function consumeEvent(Events\Event $event): void
    {
        var_dump($event);
    }
});

$emitter->dispatch(new class implements Events\Event {

});
```

The EventEmitter object is used to dispatch events into the system.

The Events object can only be used to register and unregister listeners but not emmit new events.


Filter events
-------------

Sometimes a listener should not act on every available event but only on certain ones.

This can be achieved with filters.

```php
use StephanSchuler\Events;

class Filter implements Events\Modification\Filter
{
    public function filterEvent(Events\Event $event): bool
    {
        return $event instanceof My\Special\Event;
    }
}

class Listener implements Events\Listener
{
    public function consumeEvent(Events\Event $event): void
    {
        // here we're sure its only the events we want
    }
}

assert($events instanceof Events\Events);
$events
    ->filter(new Filters())
    ->register(new Listener());
```


Map events:
-----------

When dealing with streams of data, filter usually comes with map.
I'm not sure if its particularly useful for events. We'll see.

```php
use StephanSchuler\Events;

class DerivedEvent implements Events\Event
{
    private $original;
    public function __construct(Events\Event $original)
    {
        $this->original = $original;
    }
}

class Mapper implements Events\Modification\Mapper
{
    public function mapEvent(Events\Event $event): Events\Event
    {
        return new DerivedEvent($event);
    }
}

class Listener implements Events\Listener
{
    public function consumeEvent(Events\Event $event): void
    {
        // the $event will be the derived one
    }
}

assert($events instanceof Events\Events);
$events
    ->map(new Mapper())
    ->register(new Listener());
```

Unregister events
-----------------

There's a general way of unregistering events:

```php
use StephanSchuler\Events;

assert($events instanceof Events\Events);

$listener = new class implements Events\Listener {
    public function consumeEvent(Events\Event $event): void
    {
    }
};

$events->register($listener);
$events->unregister($listener);
```

Unregistering of course can be done at a later point in time, e.g. when the first event was consumed.


Unregister shortcut
-------------------

The register call always returns a closure wrapping the corresponding unregister call.

```php
use StephanSchuler\Events;

assert($events instanceof Events\Events);

$listener = new class implements Events\Listener {
    public function consumeEvent(Events\Event $event): void
    {
    }
};

$unregister = $events->register($listener);

$unregister();
```