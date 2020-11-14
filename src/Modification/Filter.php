<?php
declare(strict_types=1);

namespace StephanSchuler\Events\Modification;

use StephanSchuler\Events\Event;

interface Filter extends ModificationStep
{
    public function filterEvent(Event $event): bool;
}