<?php

namespace Alcaeus\Graph;

use InvalidArgumentException;
use function is_string;
use function sprintf;

final class Graph
{
    /** @var array<string, Node> */
    private array $nodes = [];

    /** @var Edge */
    private array $outgoingEdges = [];

    /** @var Edge */
    private array $incomingEdges = [];

    /*
     * Node logic
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

    public function getNode(string $id): Node
    {
        return $this->nodes[$id] ?? throw new InvalidArgumentException(sprintf('Node with ID "%s" not found', $id));
    }

    public function hasNode(string $id): bool
    {
        return isset($this->nodes[$id]);
    }

    /*
     * Edge logic
     */

    public function connect(Node|string $from, Node|string $to, mixed $data = null): Edge
    {
        if (is_string($from)) {
            $from = $this->getNode($from);
        } else if ($from->graph !== $this) {
            throw new InvalidArgumentException(sprintf('Node "%s" does not belong to this graph', $from->id));
        }

        if (is_string($to)) {
            $to = $this->getNode($to);
        } else if ($to->graph !== $this) {
            throw new InvalidArgumentException(sprintf('Node "%s" does not belong to this graph', $to->id));
        }

        $edge = new Edge($from, $to, $data);
        $this->outgoingEdges[$from->id][] = $edge;
        $this->incomingEdges[$to->id][] = $edge;

        return $edge;
    }

    /** @return list<Edge> */
    public function getIncomingEdges(Node $node): array
    {
        return $this->incomingEdges[$node->id];
    }

    /** @return list<Edge> */
    public function getOutgoingEdges(Node $node): array
    {
        return $this->outgoingEdges[$node->id];
    }
}