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

    /*
     * Path finding logic
     */

    /**
     * Find all paths from one node to another using depth-first search.
     *
     * This implementation uses a modified depth-first search (DFS) algorithm to find all
     * possible paths between two nodes in the graph. The algorithm is based on the classic
     * "All Simple Paths" problem solution.
     *
     * Algorithm characteristics:
     * - Time complexity: O(V! * E) in worst case for dense graphs with many paths
     * - Space complexity: O(V) for recursion stack and visited tracking
     * - Avoids cycles by tracking visited nodes in current path
     * - Returns all simple paths (no repeated nodes within a single path)
     *
     * References:
     * - Tarjan, R. E. (1981). "A unified approach to path problems"
     * - Sedgewick, R. & Wayne, K. "Algorithms, 4th Edition" - Graph Processing
     *
     * @param Node<NodeDataType, EdgeDataType>|string $from Starting node (or node ID)
     * @param Node<NodeDataType, EdgeDataType>|string $to   Destination node (or node ID)
     *
     * @return list<Path<NodeDataType, EdgeDataType>> All paths from start to end node
     *
     * @throws InvalidArgumentException if nodes don't exist or don't belong to this graph.
     */
    public function getPaths(Node|string $from, Node|string $to): array
    {
        // Normalize inputs to Node objects
        $fromNode = is_string($from) ? $this->getNode($from) : $from;
        $toNode = is_string($to) ? $this->getNode($to) : $to;

        // Validate nodes belong to this graph
        if ($fromNode->graph !== $this) {
            throw new InvalidArgumentException(sprintf('Node "%s" does not belong to this graph', $fromNode->id));
        }

        if ($toNode->graph !== $this) {
            throw new InvalidArgumentException(sprintf('Node "%s" does not belong to this graph', $toNode->id));
        }

        $allPaths = [];
        $currentPath = [];
        $visited = [];

        $this->findAllPathsDFS($fromNode, $toNode, $currentPath, $visited, $allPaths);

        return $allPaths;
    }

    /**
     * Recursive depth-first search to find all paths between two nodes.
     *
     * This is the core pathfinding algorithm implementing a backtracking approach:
     * 1. Mark current node as visited in this path
     * 2. If we reached the destination, record the path
     * 3. Otherwise, explore all unvisited neighbors recursively
     * 4. Backtrack by unmarking the current node as visited
     *
     * The algorithm ensures we find all simple paths (no cycles within a single path)
     * while being efficient through pruning of already-visited nodes in the current path.
     *
     * @param Node<NodeDataType, EdgeDataType> $current     Current node being explored
     * @param Node<NodeDataType, EdgeDataType> $destination Target node we're trying to reach
     * @param list<Edge<NodeDataType, EdgeDataType>> $currentPath Edges in the current path being built
     * @param array<string, bool> $visited     Nodes visited in current path (for cycle detection)
     * @param list<Path<NodeDataType, EdgeDataType>> $allPaths    Accumulator for all found paths (passed by reference)
     */
    private function findAllPathsDFS(
        Node $current,
        Node $destination,
        array $currentPath,
        array $visited,
        array &$allPaths,
    ): void {
        // Mark current node as visited in this path
        $visited[$current->id] = true;

        // If we reached the destination, we found a complete path
        if ($current === $destination) {
            $startNode = empty($currentPath) ? $current : $currentPath[0]->from;
            $allPaths[] = new Path($startNode, $destination, $currentPath);

            return;
        }

        // Explore all outgoing edges from current node
        foreach ($this->getOutgoingEdges($current) as $edge) {
            $nextNode = $edge->to;

            // Only continue if we haven't visited this node in the current path
            // (this prevents infinite loops in cyclic graphs)
            if (isset($visited[$nextNode->id])) {
                continue;
            }

            // Add this edge to current path and continue recursively
            $newPath = [...$currentPath, $edge];
            $this->findAllPathsDFS($nextNode, $destination, $newPath, $visited, $allPaths);
        }
    }
}
