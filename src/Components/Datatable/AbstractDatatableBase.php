<?php

namespace App\Components\Datatable;

use App\Dto\Users\Group;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

abstract class AbstractDatatableBase extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public int $itemsPerPage = 10;

    protected ?array $apiResult = null;

    public function updatedItemsPerPage(): void
    {
        $this->page = 1;
    }

    abstract protected function instantiateForm(): FormInterface;

    abstract protected function getApiResult(): array;

    abstract public function getHeaders(): array;

    public function getItems(): array
    {
        return $this->getApiResult()['items'];
    }

    public function getTotalItems(): int
    {
        return $this->getApiResult()['totalItems'];
    }

    public function getPageCount(): int
    {
        return (int) ceil($this->getTotalItems() / $this->itemsPerPage);
    }

    public function getStartItem(): int
    {
        $total = $this->getTotalItems();
        if ($total === 0) {
            return 0;
        }
        return ($this->page - 1) * $this->itemsPerPage + 1;
    }

    public function getEndItem(): int
    {
        return min($this->page * $this->itemsPerPage, $this->getTotalItems());
    }

    /**
     * Get pages to display with ellipses where appropriate.
     *
     * @return array<int|string>
     */
    public function getVisiblePages(): array
    {
        $pageCount = $this->getPageCount();
        $current = $this->page;
        $delta = 2;
        $left = $current - $delta;
        $right = $current + $delta + 1;
        $range = [];
        $rangeWithDots = [];
        $l = null;

        for ($i = 1; $i <= $pageCount; $i++) {
            if ($i == 1 || $i == $pageCount || ($i >= $left && $i < $right)) {
                $range[] = $i;
            }
        }

        foreach ($range as $i) {
            if ($l !== null) {
                if ($i - $l === 2) {
                    $rangeWithDots[] = $l + 1;
                } elseif ($i - $l !== 1) {
                    $rangeWithDots[] = '...';
                }
            }
            $rangeWithDots[] = $i;
            $l = $i;
        }

        return $rangeWithDots;
    }

    #[LiveAction]
    public function search(): void
    {
        $this->page = 1;
    }

    #[LiveAction]
    public function reset(): void
    {
        $this->formValues = [];
        $this->page = 1;
    }

    #[LiveAction]
    public function changePage(#[LiveArg] int $page): void
    {
        $pageCount = $this->getPageCount();
        $this->page = max(1, min($page, max(1, $pageCount)));
    }
}