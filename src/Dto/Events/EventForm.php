<?php

namespace App\Dto\Events;

class EventForm
{
    public ?int $id = null;
    public string $name = '';
    public string $startDate = '';
    public string $endDate = '';
    public bool $active = false;
    public string $description = '';

    /**
     * @var list<string>
     */
    public array $tags = [];

    public static function fromEvent(Event $event): self
    {
        $form = new self();
        $form->id = $event->id;
        $form->name = $event->name ?? '';
        $form->startDate = $event->getStartDateForInput();
        $form->endDate = $event->getEndDateForInput();
        $form->active = $event->active;
        $form->description = $event->description ?? '';
        $form->tags = $event->getTagIris();

        return $form;
    }
}
