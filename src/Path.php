<?php

declare(strict_types=1);

namespace Alcaeus\Graph;

use InvalidArgumentException;

use function count;
use function in_array;
use function sprintf;

/**
 * Represents a path between two nodes in a graph.
 *
 * A path is defined as a sequence of edges that connects a start node to an end node.
 * This value object is immutable and provides methods to access path information.
 *
 * @template NodeDataType
 * @template EdgeDataType
 */
final readonly class Path
{
    /**
     * @param Node<NodeDataType, EdgeDataType> $start The starting node of the path
     * @param Node<NodeDataType, EdgeDataType> $end   The ending node of the path
     * @param list<Edge<NodeDataType, EdgeDataType>> $edges The ordered list of edges that form this path
     */
    public function __construct(
        public Node $start,
        public Node $end,
        public array $edges,
    ) {
        // Validate that edges form a valid path
        if (empty($this->edges)) {
            return;
        }

        $this->validatePath();
    }

    /**
     * Get the length of the path (number of edges).
     */
    public function getLength(): int
    {
        return count($this->edges);
    }

    /**
     * Get all nodes in the path, including start and end nodes.
     *
     * @return list<Node<NodeDataType, EdgeDataType>>
     */
    public function getNodes(): array
    {
        if (empty($this->edges)) {
            return [$this->start];
        }

        $nodes = [$this->start];
        foreach ($this->edges as $edge) {
            $nodes[] = $edge->to;
        }

        return $nodes;
    }

    /**
     * Check if this path contains a specific node.
     *
     * @param Node<NodeDataType, EdgeDataType> $node
     */
    public function containsNode(Node $node): bool
    {
        return in_array($node, $this->getNodes(), true);
    }

    /**
     * Check if this path contains a specific edge.
     *
     * @param Edge<NodeDataType, EdgeDataType> $edge
     */
    public function containsEdge(Edge $edge): bool
    {
        return in_array($edge, $this->edges, true);
    }

    /**
     * Get a string representation of the path showing node IDs.
     */
    public function __toString(): string
    {
        if (empty($this->edges)) {
            return $this->start->id;
        }

        $path = $this->start->id;
        foreach ($this->edges as $edge) {
            $path .= ' -> ' . $edge->to->id;
        }

        return $path;
    }

    /**
     * Validate that the edges form a continuous path from start to end.
     *
     * @throws InvalidArgumentException if the path is invalid.
     */
    private function validatePath(): void
    {
        $currentNode = $this->start;

        foreach ($this->edges as $i => $edge) {
            if ($edge->from !== $currentNode) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Edge at index %d does not continue from the previous node. Expected from node "%s", got "%s"',
                        $i,
                        $currentNode->id,
                        $edge->from->id,
                    ),
                );
            }

            $currentNode = $edge->to;
        }

        if ($currentNode !== $this->end) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path does not end at the expected node. Expected "%s", got "%s"',
                    $this->end->id,
                    $currentNode->id,
                ),
            );
        }
    }
}
