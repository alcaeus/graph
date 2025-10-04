<?php

declare(strict_types=1);

namespace Alcaeus\Graph;

/**
 * @template NodeDataType
 * @template EdgeDataType
 */
final class Edge
{
    // phpcs:disable
    /** @var Graph<NodeDataType, EdgeDataType> */
    public Graph $graph {
        get => $this->from->graph;
    }
    // phpcs:enable

    /**
     * @param Node<NodeDataType, EdgeDataType> $from
     * @param Node<NodeDataType, EdgeDataType> $to
     * @param EdgeDataType $data
     */
    public function __construct(
        public readonly Node $from,
        public readonly Node $to,
        public readonly mixed $data = null,
    ) {
    }
}
