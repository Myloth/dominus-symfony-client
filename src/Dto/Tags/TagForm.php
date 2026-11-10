<?php

namespace App\Dto\Tags;

class TagForm
{
    public ?int $id = null;
    public string $slug = '';
    public string $name = '';

    /**
     * @var list<string>
     */
    public array $entityTypes = [];

    public string $category = '';

    public static function fromTag(Tag $tag): self
    {
        $form = new self();
        $form->id = $tag->id;
        $form->slug = $tag->slug ?? '';
        $form->name = $tag->name ?? '';
        $form->entityTypes = $tag->getEntityTypeIris();
        $form->category = $tag->category ?? '';

        return $form;
    }
}
