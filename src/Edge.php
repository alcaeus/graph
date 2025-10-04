<?php

declare(strict_types=1);

namespace Alcaeus\Graph;

final class Edge
{
    // phpcs:disable
    public Graph $graph {
        get => $this->from->graph;
    }
    // phpcs:enable

    public function __construct(
        public readonly Node $from,
        public readonly Node $to,
        public readonly mixed $data = null,
    ) {
    }
}
