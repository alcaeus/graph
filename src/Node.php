<?php

namespace Alcaeus\Graph;

final class Node
{
    /** @var list<Edge> */
    public array $incomingEdges {
        get {
            return $this->graph->getIncomingEdges($this);
        }
    }

    /** @var list<Edge> */
    public array $outgoingEdges {
        get {
            return $this->graph->getOutgoingEdges($this);
        }
    }

    public function __construct(
        public readonly Graph $graph,
        public readonly string $id,
        public readonly mixed $data = null,
    ) {}

    public function connect(Node $to, mixed $data = null): Edge
    {
        return $this->graph->connect($this, $to, $data);
    }
}