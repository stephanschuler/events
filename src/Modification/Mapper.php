<?php
declare(strict_types=1);

namespace StephanSchuler\Events\Modification;

use StephanSchuler\Events\Event;

interface Mapper extends ModificationStep
{
    public function mapEvent(Event $event): Event;
}