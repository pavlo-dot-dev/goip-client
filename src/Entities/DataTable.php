<?php

namespace PavloDotDev\GoipClient\Entities;

use PavloDotDev\GoipClient\GoipClient;
use Symfony\Component\DomCrawler\Crawler;

class DataTable implements \Iterator
{
    protected int $page = 0;
    protected int $totalItems = 0;
    protected int $totalPages = 1;

    protected array $items = [];
    protected array $columns = [];
    protected int $position = 0;


    public function __construct(
        protected readonly GoipClient $api,
        protected readonly string $path,
        protected readonly ?array $get = null,
        protected readonly ?array $post = null,
    ) {
        $this->load(1, true);
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }

    public function totalPages(): int
    {
        return $this->totalPages;
    }

    public function currentPage(): int
    {
        return $this->page;
    }

    protected function load(int $page = 1, bool $init = false): void
    {
        $crawler = $this->api->request($this->path, [
            ...($this->get ?? []),
            'page' => $page,
        ], $this->post);

        $paginationTable = $crawler->filter('form[name="myform"] table:last-child');
        $pagesInfo = $paginationTable->filter('strong')->text();
        if ($init) {
            $this->totalItems = $paginationTable->filter('b:first-child')->text();
            $this->totalPages = explode('/', $pagesInfo)[1] ?? 1;
            $this->columns = $crawler
                ->filter('form[name="myform"] table:first-child > tr:first-child')
                ->first()
                ->filter('td')
                ->each(fn(Crawler $column) => str_replace(" ", '_', mb_strtolower($column->text())));
            $this->columns[] = 'raw';
        }
        $this->page = explode('/', $pagesInfo)[0] ?? 1;

        $rows = $crawler
            ->filter('form[name="myform"] table:first-child > tr:not(:first-child)')
            ->each(fn(Crawler $row) => [
                ...$row->filter('td')->each(
                    fn(Crawler $column) => $column->filter('input')->count() ? $column->filter(
                        'input:first-child'
                    )->attr('value') : $column->text()
                ),
                $row
            ]);

        $columnsCount = count($this->columns);
        foreach ($rows as $row) {
            if (count($row) === $columnsCount) {
                $this->items[] = $row;
            }
        }
    }

    public function current(): mixed
    {
        return array_combine($this->columns, $this->items[$this->position]);
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function valid(): bool
    {
        if (!isset($this->items[$this->position])) {
            if ($this->page < $this->totalPages) {
                $this->load($this->page + 1);
            }
        }

        return isset($this->items[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'columns' => $this->columns,
            'page' => [
                'total' => $this->totalPages,
                'current' => $this->page,
            ],
            'total' => $this->totalItems,
        ];
    }
}
