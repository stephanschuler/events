<?php
declare(strict_types=1);

namespace StephanSchuler\Events;

interface Listener
{
    public function consumeEvent(Event $event): void;
}