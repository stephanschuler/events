<?php
declare(strict_types=1);

namespace StephanSchuler\Events\Modification;

use StephanSchuler\Events\Event;

final class Modifier
{
    private $steps;

    private function __construct(ModificationStep...$steps)
    {
        $this->steps = $steps;
    }

    public static function create(): self
    {
        return new static();
    }

    public function transform(Event $event): ?Event
    {
        foreach ($this->steps as $step) {
            if ($step instanceof Filter) {
                if (!$step->filterEvent($event)) {
                    return null;
                }
            }
            if ($step instanceof Mapper) {
                $event = $step->mapEvent($event);
            }
        }
        return $event;
    }

    public function withStep(ModificationStep $step): self
    {
        $steps = $this->steps;
        $steps[] = $step;
        return new static(... $steps);
    }
}