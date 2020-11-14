<?php
declare(strict_types=1);

namespace StephanSchuler\Events;

interface Listener
{
    public function __invoke(Event $data): void;
}