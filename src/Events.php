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
    private $transformation;

    private function __construct(EventEmitter $eventEmitter, Modifier $transformation)
    {
        $this->eventEmitter = $eventEmitter;
        $this->transformation = $transformation;
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
        return $this->eventEmitter->register($listener, $this->transformation);
    }

    public function unregister(Listener $listener, Modifier $condition = null): void
    {
        $this->eventEmitter->unregister($listener, $condition);
    }

    protected function modify(ModificationStep $modifier): self
    {
        $transformation = $this->transformation->withStep($modifier);
        return new static($this->eventEmitter, $transformation);
    }
}
