<?php
declare(strict_types=1);

namespace StephanSchuler\Events;

use StephanSchuler\Events\Modification\Filter;
use StephanSchuler\Events\Modification\Mapper;
use StephanSchuler\Events\Modification\ModificationStep;
use StephanSchuler\Events\Modification\Modifier;

class Events
{
    private $eventEmitter;
    private $modifier;

    private function __construct(EventEmitter $eventEmitter, Modifier $modifier)
    {
        $this->eventEmitter = $eventEmitter;
        $this->modifier = $modifier;
    }

    public static function create(EventEmitter $eventEmitter): self
    {
        return new static($eventEmitter, Modifier::create());
    }

    public function filter(Filter $filter): self
    {
        return $this->modify($filter);
    }

    public function map(Mapper $mapper): self
    {
        return $this->modify($mapper);
    }

    public function register(Listener $listener): callable
    {
        return $this->eventEmitter->register($listener, $this->modifier);
    }

    public function unregister(Listener $listener, Modifier $modifier = null): void
    {
        $this->eventEmitter->unregister($listener, $modifier);
    }

    protected function modify(ModificationStep $modificationStep): self
    {
        $modifier = $this->modifier->withStep($modificationStep);
        return new static($this->eventEmitter, $modifier);
    }
}
