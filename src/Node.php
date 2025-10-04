<?php

declare(strict_types=1);

namespace Alcaeus\Graph;

/**
 * @template NodeDataType
 * @template EdgeDataType
 */
final class Node
{
    // phpcs:disable
    /** @var list<Edge<NodeDataType, EdgeDataType>> */
    public array $incomingEdges {
        get => $this->graph->getIncomingEdges($this);
    }

    /** @var list<Edge<NodeDataType, EdgeDataType>> */
    public array $outgoingEdges {
        get => $this->graph->getOutgoingEdges($this);
    }
    // phpcs:enable

    /**
     * @param Graph<NodeDataType, EdgeDataType> $graph
     * @param NodeDataType $data
     */
    public function __construct(
        public readonly Graph $graph,
        public readonly string $id,
        public readonly mixed $data = null,
    ) {
    }

    /**
     * @param Node<NodeDataType, EdgeDataType> $to
     * @param EdgeDataType $data
     *
     * @return Edge<NodeDataType, EdgeDataType>
     */
    public function connect(Node $to, mixed $data = null): Edge
    {
        return $this->graph->connect($this, $to, $data);
    }
}
