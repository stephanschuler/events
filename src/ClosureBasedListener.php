<?php
declare(strict_types=1);

namespace StephanSchuler\Events;

class ClosureBasedListener implements Listener
{
    private $consume;

    private function __construct(callable $consume)
    {
        $this->consume = $consume;
    }

    public static function create(callable $consume): self
    {
        return new static($consume);
    }

    public function __invoke(Event $data): void
    {
        ($this->consume)($data);
    }
}