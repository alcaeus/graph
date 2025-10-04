<?php

namespace Alcaeus\Graph;

final class Edge
{
    public Graph $graph {
        get {
            return $this->from->graph;
        }
    }

    public function __construct(
        public readonly Node $from,
        public readonly Node $to,
        public readonly mixed $data = null,
    ) {}
}