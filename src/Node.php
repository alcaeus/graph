<?php

declare(strict_types=1);

namespace Alcaeus\Graph;

final class Node
{
    // phpcs:disable
    /** @var list<Edge> */
    public array $incomingEdges {
        get => $this->graph->getIncomingEdges($this);
    }

    /** @var list<Edge> */
    public array $outgoingEdges {
        get => $this->graph->getOutgoingEdges($this);
    }
    // phpcs:enable

    public function __construct(
        public readonly Graph $graph,
        public readonly string $id,
        public readonly mixed $data = null,
    ) {
    }

    public function connect(Node $to, mixed $data = null): Edge
    {
        return $this->graph->connect($this, $to, $data);
    }
}
