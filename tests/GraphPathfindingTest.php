<?php

declare(strict_types=1);

namespace Alcaeus\Graph\Tests;

use Alcaeus\Graph\Graph;
use Alcaeus\Graph\Path;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_map;
use function microtime;
use function sort;
use function sprintf;
use function strcmp;
use function usort;

#[CoversClass(Graph::class)]
class GraphPathfindingTest extends TestCase
{
    private Graph $graph;

    protected function setUp(): void
    {
        $this->graph = new Graph();
    }

    public function testGetPathsWithNoConnection(): void
    {
        $nodeA = $this->graph->addNode('A');
        $nodeB = $this->graph->addNode('B');

        $paths = $this->graph->getPaths($nodeA, $nodeB);

        $this->assertEmpty($paths);
    }

    public function testGetPathsFromNodeToItself(): void
    {
        $nodeA = $this->graph->addNode('A');

        $paths = $this->graph->getPaths($nodeA, $nodeA);

        $this->assertCount(1, $paths);
        $this->assertInstanceOf(Path::class, $paths[0]);
        $this->assertSame($nodeA, $paths[0]->start);
        $this->assertSame($nodeA, $paths[0]->end);
        $this->assertEquals(0, $paths[0]->getLength());
    }

    public function testGetPathsWithDirectConnection(): void
    {
        $nodeA = $this->graph->addNode('A');
        $nodeB = $this->graph->addNode('B');
        $edge = $this->graph->connect($nodeA, $nodeB, 'direct');

        $paths = $this->graph->getPaths($nodeA, $nodeB);

        $this->assertCount(1, $paths);
        $this->assertSame($nodeA, $paths[0]->start);
        $this->assertSame($nodeB, $paths[0]->end);
        $this->assertEquals(1, $paths[0]->getLength());
        $this->assertSame($edge, $paths[0]->edges[0]);
    }

    public function testGetPathsWithTwoHopPath(): void
    {
        $nodeA = $this->graph->addNode('A');
        $nodeB = $this->graph->addNode('B');
        $nodeC = $this->graph->addNode('C');

        $edge1 = $this->graph->connect($nodeA, $nodeB, 'first');
        $edge2 = $this->graph->connect($nodeB, $nodeC, 'second');

        $paths = $this->graph->getPaths($nodeA, $nodeC);

        $this->assertCount(1, $paths);
        $this->assertSame($nodeA, $paths[0]->start);
        $this->assertSame($nodeC, $paths[0]->end);
        $this->assertEquals(2, $paths[0]->getLength());
        $this->assertSame($edge1, $paths[0]->edges[0]);
        $this->assertSame($edge2, $paths[0]->edges[1]);
    }

    public function testGetPathsWithMultiplePaths(): void
    {
        // Create a diamond-shaped graph: A -> B -> D, A -> C -> D
        $nodeA = $this->graph->addNode('A');
        $nodeB = $this->graph->addNode('B');
        $nodeC = $this->graph->addNode('C');
        $nodeD = $this->graph->addNode('D');

        $this->graph->connect($nodeA, $nodeB, 'A->B');
        $this->graph->connect($nodeA, $nodeC, 'A->C');
        $this->graph->connect($nodeB, $nodeD, 'B->D');
        $this->graph->connect($nodeC, $nodeD, 'C->D');

        $paths = $this->graph->getPaths($nodeA, $nodeD);

        $this->assertCount(2, $paths);

        // Sort paths by their string representation for consistent testing
        usort($paths, static fn ($a, $b) => strcmp((string) $a, (string) $b));

        $this->assertEquals('A -> B -> D', (string) $paths[0]);
        $this->assertEquals('A -> C -> D', (string) $paths[1]);
    }

    public function testGetPathsWithCyclicGraph(): void
    {
        // Create a graph with a cycle: A -> B -> C -> A, plus A -> D
        $nodeA = $this->graph->addNode('A');
        $nodeB = $this->graph->addNode('B');
        $nodeC = $this->graph->addNode('C');
        $nodeD = $this->graph->addNode('D');

        $this->graph->connect($nodeA, $nodeB, 'A->B');
        $this->graph->connect($nodeB, $nodeC, 'B->C');
        $this->graph->connect($nodeC, $nodeA, 'C->A'); // Creates cycle
        $this->graph->connect($nodeA, $nodeD, 'A->D');

        $paths = $this->graph->getPaths($nodeA, $nodeD);

        // Should find the direct path A -> D without getting stuck in the cycle
        $this->assertCount(1, $paths);
        $this->assertEquals('A -> D', (string) $paths[0]);
    }

    public function testGetPathsWithMultipleEdgesBetweenSameNodes(): void
    {
        $nodeA = $this->graph->addNode('A');
        $nodeB = $this->graph->addNode('B');

        // Create multiple edges between the same nodes
        $edge1 = $this->graph->connect($nodeA, $nodeB, 'first');
        $edge2 = $this->graph->connect($nodeA, $nodeB, 'second');

        $paths = $this->graph->getPaths($nodeA, $nodeB);

        // Should find multiple paths, one for each edge
        $this->assertCount(2, $paths);
        $this->assertTrue($paths[0]->containsEdge($edge1) || $paths[0]->containsEdge($edge2));
        $this->assertTrue($paths[1]->containsEdge($edge1) || $paths[1]->containsEdge($edge2));
        $this->assertNotSame($paths[0]->edges[0], $paths[1]->edges[0]);
    }

    public function testGetPathsWithComplexGraph(): void
    {
        // Create a more complex graph with multiple interconnected paths
        $nodeA = $this->graph->addNode('A');
        $nodeB = $this->graph->addNode('B');
        $nodeC = $this->graph->addNode('C');
        $nodeD = $this->graph->addNode('D');
        $nodeE = $this->graph->addNode('E');

        // Create connections: A->B->E, A->C->E, A->D->E, B->D->E
        $this->graph->connect($nodeA, $nodeB, 'A->B');
        $this->graph->connect($nodeA, $nodeC, 'A->C');
        $this->graph->connect($nodeA, $nodeD, 'A->D');
        $this->graph->connect($nodeB, $nodeE, 'B->E');
        $this->graph->connect($nodeC, $nodeE, 'C->E');
        $this->graph->connect($nodeD, $nodeE, 'D->E');
        $this->graph->connect($nodeB, $nodeD, 'B->D');

        $paths = $this->graph->getPaths($nodeA, $nodeE);

        // Should find 4 paths: A->B->E, A->C->E, A->D->E, A->B->D->E
        $this->assertCount(4, $paths);

        $pathStrings = array_map(static fn ($path) => (string) $path, $paths);
        sort($pathStrings);

        $this->assertContains('A -> B -> D -> E', $pathStrings);
        $this->assertContains('A -> B -> E', $pathStrings);
        $this->assertContains('A -> C -> E', $pathStrings);
        $this->assertContains('A -> D -> E', $pathStrings);
    }

    public function testGetPathsWithStringNodeIds(): void
    {
        $nodeA = $this->graph->addNode('A');
        $nodeB = $this->graph->addNode('B');
        $this->graph->connect($nodeA, $nodeB, 'edge');

        // Test using string IDs instead of Node objects
        $paths = $this->graph->getPaths('A', 'B');

        $this->assertCount(1, $paths);
        $this->assertEquals('A -> B', (string) $paths[0]);
    }

    public function testGetPathsThrowsExceptionForNonExistentNode(): void
    {
        $nodeA = $this->graph->addNode('A');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node with ID "B" not found');

        $this->graph->getPaths($nodeA, 'B');
    }

    public function testGetPathsThrowsExceptionForNodeFromDifferentGraph(): void
    {
        $otherGraph = new Graph();
        $nodeA = $this->graph->addNode('A');
        $nodeB = $otherGraph->addNode('B');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node "B" does not belong to this graph');

        $this->graph->getPaths($nodeA, $nodeB);
    }

    public function testGetPathsPerformanceWithLargeGraph(): void
    {
        // Create a linear chain of nodes to test performance
        $nodeCount = 50;
        $nodes = [];

        for ($i = 0; $i < $nodeCount; $i++) {
            $nodes[$i] = $this->graph->addNode(sprintf('node_%s', $i));
            if ($i <= 0) {
                continue;
            }

            $this->graph->connect($nodes[$i - 1], $nodes[$i], sprintf('edge_%s', $i));
        }

        $startTime = microtime(true);
        $paths = $this->graph->getPaths($nodes[0], $nodes[$nodeCount - 1]);
        $endTime = microtime(true);

        // Should find exactly one path in a linear graph
        $this->assertCount(1, $paths);
        $this->assertEquals($nodeCount - 1, $paths[0]->getLength());

        // Performance check: should complete in reasonable time (less than 1 second)
        $this->assertLessThan(1.0, $endTime - $startTime);
    }

    public function testGetPathsWithSelfLoop(): void
    {
        $nodeA = $this->graph->addNode('A');
        $nodeB = $this->graph->addNode('B');

        // Create a self-loop and a path to another node
        $this->graph->connect($nodeA, $nodeA, 'self-loop');
        $this->graph->connect($nodeA, $nodeB, 'A->B');

        $paths = $this->graph->getPaths($nodeA, $nodeB);

        // Should find the direct path, ignoring the self-loop
        $this->assertCount(1, $paths);
        $this->assertEquals('A -> B', (string) $paths[0]);
    }
}
