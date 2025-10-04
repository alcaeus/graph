<?php

declare(strict_types=1);

namespace Alcaeus\Graph\Tests;

use Alcaeus\Graph\Graph;
use Alcaeus\Graph\Node;
use Alcaeus\Graph\Path;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Path::class)]
class PathTest extends TestCase
{
    private Graph $graph;
    private Node $nodeA;
    private Node $nodeB;
    private Node $nodeC;

    protected function setUp(): void
    {
        $this->graph = new Graph();
        $this->nodeA = $this->graph->addNode('A', 'dataA');
        $this->nodeB = $this->graph->addNode('B', 'dataB');
        $this->nodeC = $this->graph->addNode('C', 'dataC');
    }

    public function testCanCreateEmptyPath(): void
    {
        $path = new Path($this->nodeA, $this->nodeA, []);

        $this->assertSame($this->nodeA, $path->start);
        $this->assertSame($this->nodeA, $path->end);
        $this->assertEmpty($path->edges);
        $this->assertEquals(0, $path->getLength());
    }

    public function testCanCreateSingleEdgePath(): void
    {
        $edge = $this->graph->connect($this->nodeA, $this->nodeB, 'edge data');
        $path = new Path($this->nodeA, $this->nodeB, [$edge]);

        $this->assertSame($this->nodeA, $path->start);
        $this->assertSame($this->nodeB, $path->end);
        $this->assertCount(1, $path->edges);
        $this->assertSame($edge, $path->edges[0]);
        $this->assertEquals(1, $path->getLength());
    }

    public function testCanCreateMultipleEdgePath(): void
    {
        $edge1 = $this->graph->connect($this->nodeA, $this->nodeB, 'edge1');
        $edge2 = $this->graph->connect($this->nodeB, $this->nodeC, 'edge2');
        $path = new Path($this->nodeA, $this->nodeC, [$edge1, $edge2]);

        $this->assertSame($this->nodeA, $path->start);
        $this->assertSame($this->nodeC, $path->end);
        $this->assertCount(2, $path->edges);
        $this->assertEquals(2, $path->getLength());
    }

    public function testGetNodesForEmptyPath(): void
    {
        $path = new Path($this->nodeA, $this->nodeA, []);
        $nodes = $path->getNodes();

        $this->assertCount(1, $nodes);
        $this->assertSame($this->nodeA, $nodes[0]);
    }

    public function testGetNodesForSingleEdgePath(): void
    {
        $edge = $this->graph->connect($this->nodeA, $this->nodeB, 'edge data');
        $path = new Path($this->nodeA, $this->nodeB, [$edge]);
        $nodes = $path->getNodes();

        $this->assertCount(2, $nodes);
        $this->assertSame($this->nodeA, $nodes[0]);
        $this->assertSame($this->nodeB, $nodes[1]);
    }

    public function testGetNodesForMultipleEdgePath(): void
    {
        $edge1 = $this->graph->connect($this->nodeA, $this->nodeB, 'edge1');
        $edge2 = $this->graph->connect($this->nodeB, $this->nodeC, 'edge2');
        $path = new Path($this->nodeA, $this->nodeC, [$edge1, $edge2]);
        $nodes = $path->getNodes();

        $this->assertCount(3, $nodes);
        $this->assertSame($this->nodeA, $nodes[0]);
        $this->assertSame($this->nodeB, $nodes[1]);
        $this->assertSame($this->nodeC, $nodes[2]);
    }

    public function testContainsNode(): void
    {
        $edge1 = $this->graph->connect($this->nodeA, $this->nodeB, 'edge1');
        $edge2 = $this->graph->connect($this->nodeB, $this->nodeC, 'edge2');
        $path = new Path($this->nodeA, $this->nodeC, [$edge1, $edge2]);

        $this->assertTrue($path->containsNode($this->nodeA));
        $this->assertTrue($path->containsNode($this->nodeB));
        $this->assertTrue($path->containsNode($this->nodeC));

        $nodeD = $this->graph->addNode('D', 'dataD');
        $this->assertFalse($path->containsNode($nodeD));
    }

    public function testContainsEdge(): void
    {
        $edge1 = $this->graph->connect($this->nodeA, $this->nodeB, 'edge1');
        $edge2 = $this->graph->connect($this->nodeB, $this->nodeC, 'edge2');
        $edge3 = $this->graph->connect($this->nodeA, $this->nodeC, 'edge3');

        $path = new Path($this->nodeA, $this->nodeC, [$edge1, $edge2]);

        $this->assertTrue($path->containsEdge($edge1));
        $this->assertTrue($path->containsEdge($edge2));
        $this->assertFalse($path->containsEdge($edge3));
    }

    public function testToStringForEmptyPath(): void
    {
        $path = new Path($this->nodeA, $this->nodeA, []);

        $this->assertEquals('A', (string) $path);
    }

    public function testToStringForSingleEdgePath(): void
    {
        $edge = $this->graph->connect($this->nodeA, $this->nodeB, 'edge data');
        $path = new Path($this->nodeA, $this->nodeB, [$edge]);

        $this->assertEquals('A -> B', (string) $path);
    }

    public function testToStringForMultipleEdgePath(): void
    {
        $edge1 = $this->graph->connect($this->nodeA, $this->nodeB, 'edge1');
        $edge2 = $this->graph->connect($this->nodeB, $this->nodeC, 'edge2');
        $path = new Path($this->nodeA, $this->nodeC, [$edge1, $edge2]);

        $this->assertEquals('A -> B -> C', (string) $path);
    }

    public function testValidationFailsWhenEdgesDoNotConnect(): void
    {
        $edge1 = $this->graph->connect($this->nodeA, $this->nodeB, 'edge1');
        $edge2 = $this->graph->connect($this->nodeA, $this->nodeC, 'edge2'); // Doesn't continue from B

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Edge at index 1 does not continue from the previous node');

        new Path($this->nodeA, $this->nodeC, [$edge1, $edge2]);
    }

    public function testValidationFailsWhenPathDoesNotEndAtExpectedNode(): void
    {
        $edge = $this->graph->connect($this->nodeA, $this->nodeB, 'edge');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path does not end at the expected node');

        new Path($this->nodeA, $this->nodeC, [$edge]); // Edge goes to B, not C
    }

    public function testValidationPassesForValidPath(): void
    {
        $edge1 = $this->graph->connect($this->nodeA, $this->nodeB, 'edge1');
        $edge2 = $this->graph->connect($this->nodeB, $this->nodeC, 'edge2');

        // Should not throw any exception
        $path = new Path($this->nodeA, $this->nodeC, [$edge1, $edge2]);

        $this->assertSame($this->nodeA, $path->start);
        $this->assertSame($this->nodeC, $path->end);
        $this->assertCount(2, $path->edges);
    }
}
