# Graph Data Structure Library

[![Tests](https://github.com/alcaeus/graph/workflows/Tests/badge.svg)](https://github.com/alcaeus/graph/actions)
[![PHPStan](https://github.com/alcaeus/graph/workflows/PHPStan/badge.svg)](https://github.com/alcaeus/graph/actions)
[![PHP CS](https://github.com/alcaeus/graph/workflows/PHP%20CS/badge.svg)](https://github.com/alcaeus/graph/actions)

> **⚠️ EXPERIMENTAL LIBRARY**  
> This library is currently experimental and not yet ready for production use. 
> The API may change significantly between versions. Use at your own risk.

A sophisticated graph data structure implementation in PHP with advanced pathfinding algorithms and intelligent caching.

## Features

- **Generic Graph Structure**: Nodes and edges can store arbitrary data
- **Optimized Pathfinding**: Advanced DFS algorithm with comprehensive segment caching
- **Algorithm Architecture**: Clean separation between graph structure and algorithms
- **Type Safety**: Full PHPStan level max compliance with strict type checking
- **PHP 8.4+ Ready**: Leverages modern PHP features including property hooks

## Requirements

- PHP 8.4 or higher

## Installation

```bash
composer require alcaeus/graph
```

## Basic Usage

### Creating a Graph

```php
use Alcaeus\Graph\Graph;

// Create a new graph
$graph = new Graph();

// Add nodes with optional data
$nodeA = $graph->addNode('A', 'Node A data');
$nodeB = $graph->addNode('B', 'Node B data');
$nodeC = $graph->addNode('C');

// Connect nodes with edges (also with optional data)
$edgeAB = $graph->connect($nodeA, $nodeB, 'Connection A to B');
$edgeBC = $graph->connect('B', 'C', 'Connection B to C'); // Can use node IDs
$edgeAC = $graph->connect($nodeA, $nodeC); // No edge data
```

### Exploring Connections

```php
// Get incoming and outgoing edges for a node
$incomingEdges = $graph->getIncomingEdges($nodeB);
$outgoingEdges = $graph->getOutgoingEdges($nodeA);

// Access node and edge data
echo $nodeA->data; // "Node A data"
echo $edgeAB->data; // "Connection A to B"

// Navigate the graph
foreach ($outgoingEdges as $edge) {
    echo "From: {$edge->from->id} -> To: {$edge->to->id}\\n";
}
```

### Advanced Pathfinding

The library includes an optimized pathfinding algorithm with intelligent caching:

```php
// Find all paths between two nodes
$paths = $graph->getPaths($nodeA, $nodeC);

foreach ($paths as $path) {
    echo "Path: {$path}\\n"; // "A -> B -> C" or "A -> C"
    
    // Access path details
    echo "Start: {$path->start->id}\\n";
    echo "End: {$path->end->id}\\n";
    echo "Length: " . count($path->edges) . " edges\\n";
    
    // Iterate through edges in the path
    foreach ($path->edges as $edge) {
        echo "  {$edge->from->id} -> {$edge->to->id}\\n";
    }
}
```

### Complex Graph Example

```php
// Create a more complex graph
$graph = new Graph();

// Add nodes representing cities
$paris = $graph->addNode('PAR', ['name' => 'Paris', 'country' => 'France']);
$london = $graph->addNode('LON', ['name' => 'London', 'country' => 'UK']);
$berlin = $graph->addNode('BER', ['name' => 'Berlin', 'country' => 'Germany']);
$rome = $graph->addNode('ROM', ['name' => 'Rome', 'country' => 'Italy']);

// Add connections with travel data
$graph->connect($paris, $london, ['distance' => 344, 'mode' => 'flight']);
$graph->connect($paris, $berlin, ['distance' => 878, 'mode' => 'train']);
$graph->connect($london, $berlin, ['distance' => 933, 'mode' => 'flight']);
$graph->connect($berlin, $rome, ['distance' => 1181, 'mode' => 'flight']);
$graph->connect($paris, $rome, ['distance' => 1105, 'mode' => 'flight']);

// Find all possible routes from Paris to Rome
$routes = $graph->getPaths($paris, $rome);

echo "Found " . count($routes) . " routes from Paris to Rome:\\n";
foreach ($routes as $i => $route) {
    echo ($i + 1) . ". {$route}\\n";
    
    $totalDistance = 0;
    foreach ($route->edges as $edge) {
        $totalDistance += $edge->data['distance'];
    }
    echo "   Total distance: {$totalDistance} km\\n";
}
```

### Using with Typed Data

The library supports PHP generics for type-safe operations:

```php
/**
 * @var Graph<string, array{distance: int, mode: string}> $typedGraph
 */
$typedGraph = new Graph();

// Node data must be string, edge data must be array with distance and mode
$nodeA = $typedGraph->addNode('A', 'City A');
$nodeB = $typedGraph->addNode('B', 'City B');
$edge = $typedGraph->connect($nodeA, $nodeB, ['distance' => 100, 'mode' => 'car']);
```

## Performance Features

The library includes several performance optimizations:

- **Segment Caching**: Path segments are cached during traversal for maximum reuse
- **Algorithm Separation**: Algorithms are instantiated per graph instance and cache independently
- **Lazy Evaluation**: Algorithm instances are created only when needed
- **Cache Invalidation**: Caches are automatically cleared when graph structure changes

## Development

### Running Tests

```bash
vendor/bin/phpunit
```

### Code Quality

```bash
# Run PHPStan static analysis
vendor/bin/phpstan analyse

# Check code style
vendor/bin/phpcs

# Fix code style
vendor/bin/phpcbf
```

## Architecture

The library follows clean architecture principles:

- **Graph**: Core graph structure management
- **Node/Edge**: Basic graph elements with data storage
- **Path**: Value object representing a path through the graph
- **Algorithms**: Separated algorithms (PathFinder) with independent caching

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributing

This is an experimental library. Contributions are welcome, but please note that the API may change significantly between versions.
