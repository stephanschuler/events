<?php
declare(strict_types=1);

namespace StephanSchuler\Events;

use StephanSchuler\Events\Modification\Modifier;

class Binding
{
    private $consumer;
    private $modifier;

    public function __construct(Listener $consumer, Modifier $modifier)
    {
        $this->consumer = $consumer;
        $this->modifier = $modifier;
    }

    public static function create(Listener $consumer, Modifier $modifier): self
    {
        return new static($consumer, $modifier);
    }

    public function getListener(): ?Listener
    {
        return $this->consumer;
    }

    public function getModifier(): Modifier
    {
        return $this->modifier;
    }
}