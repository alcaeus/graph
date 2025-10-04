<?php

declare(strict_types=1);

namespace Alcaeus\Graph;

use InvalidArgumentException;

use function is_string;
use function sprintf;

/**
 * @template NodeDataType
 * @template EdgeDataType
 */
final class Graph
{
    /** @var array<string, Node<NodeDataType, EdgeDataType>> */
    private array $nodes = [];

    /** @var array<string, list<Edge<NodeDataType, EdgeDataType>>> */
    private array $outgoingEdges = [];

    /** @var array<string, list<Edge<NodeDataType, EdgeDataType>>> */
    private array $incomingEdges = [];

    /**
     * @param NodeDataType $data
     *
     * @return Node<NodeDataType, EdgeDataType>
     */
    public function addNode(string $id, mixed $data = null): Node
    {
        if ($this->hasNode($id)) {
            throw new InvalidArgumentException(sprintf('Node with ID "%s" already exists', $id));
        }

        $this->nodes[$id] = new Node($this, $id, $data);
        $this->incomingEdges[$id] = [];
        $this->outgoingEdges[$id] = [];

        return $this->nodes[$id];
    }

    /** @return Node<NodeDataType, EdgeDataType> */
    public function getNode(string $id): Node
    {
        return $this->nodes[$id] ?? throw new InvalidArgumentException(sprintf('Node with ID "%s" not found', $id));
    }

    public function hasNode(string $id): bool
    {
        return isset($this->nodes[$id]);
    }

    /**
     * @param Node<NodeDataType, EdgeDataType>|string $from
     * @param Node<NodeDataType, EdgeDataType>|string $to
     * @param EdgeDataType $data
     *
     * @return Edge<NodeDataType, EdgeDataType>
     */
    public function connect(Node|string $from, Node|string $to, mixed $data = null): Edge
    {
        if (is_string($from)) {
            $from = $this->getNode($from);
        } elseif ($from->graph !== $this) {
            throw new InvalidArgumentException(sprintf('Node "%s" does not belong to this graph', $from->id));
        }

        if (is_string($to)) {
            $to = $this->getNode($to);
        } elseif ($to->graph !== $this) {
            throw new InvalidArgumentException(sprintf('Node "%s" does not belong to this graph', $to->id));
        }

        $edge = new Edge($from, $to, $data);
        $this->outgoingEdges[$from->id][] = $edge;
        $this->incomingEdges[$to->id][] = $edge;

        return $edge;
    }

    /**
     * @param Node<NodeDataType, EdgeDataType> $node
     *
     * @return list<Edge<NodeDataType, EdgeDataType>>
     */
    public function getIncomingEdges(Node $node): array
    {
        return $this->incomingEdges[$node->id];
    }

    /**
     * @param Node<NodeDataType, EdgeDataType> $node
     *
     * @return list<Edge<NodeDataType, EdgeDataType>>
     */
    public function getOutgoingEdges(Node $node): array
    {
        return $this->outgoingEdges[$node->id];
    }
}
