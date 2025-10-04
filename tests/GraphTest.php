<?php

declare(strict_types=1);

namespace Alcaeus\Graph\Tests;

use Alcaeus\Graph\Graph;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Graph::class)]
class GraphTest extends TestCase
{
    private Graph $graph;

    protected function setUp(): void
    {
        $this->graph = new Graph();
    }

    public function testCanCreateEmptyGraph(): void
    {
        $graph = new Graph();

        $this->assertInstanceOf(Graph::class, $graph);
    }

    public function testCanAddNode(): void
    {
        $node = $this->graph->addNode('user-1', ['name' => 'John']);

        $this->assertTrue($this->graph->hasNode('user-1'));
        $this->assertSame($node, $this->graph->getNode('user-1'));
    }

    public function testCanAddNodeWithoutData(): void
    {
        $node = $this->graph->addNode('user-1');

        $this->assertTrue($this->graph->hasNode('user-1'));
        $this->assertSame($node, $this->graph->getNode('user-1'));
        $this->assertNull($node->data);
    }

    public function testAddingNodeWithDuplicateIdThrowsException(): void
    {
        $this->graph->addNode('duplicate-id', 'data1');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node with ID "duplicate-id" already exists');

        $this->graph->addNode('duplicate-id', 'data2');
    }

    public function testHasNodeReturnsTrueForExistingNode(): void
    {
        $this->graph->addNode('test-node', 'data');

        $this->assertTrue($this->graph->hasNode('test-node'));
    }

    public function testHasNodeReturnsFalseForNonExistentNode(): void
    {
        $this->assertFalse($this->graph->hasNode('non-existent'));
    }

    public function testGetNodeReturnsCorrectNode(): void
    {
        $node = $this->graph->addNode('test-node', 'test data');

        $retrievedNode = $this->graph->getNode('test-node');

        $this->assertSame($node, $retrievedNode);
    }

    public function testGetNodeThrowsExceptionForNonExistentNode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node with ID "non-existent" not found');

        $this->graph->getNode('non-existent');
    }

    public function testCanConnectTwoNodes(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');

        $edge = $this->graph->connect($node1, $node2, 'connection data');

        $this->assertSame($node1, $edge->from);
        $this->assertSame($node2, $edge->to);
        $this->assertEquals('connection data', $edge->data);
        $this->assertSame($this->graph, $edge->graph);
    }

    public function testCanConnectTwoNodesWithId(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');

        $edge = $this->graph->connect('node-1', 'node-2', 'connection data');

        $this->assertSame($node1, $edge->from);
        $this->assertSame($node2, $edge->to);
        $this->assertEquals('connection data', $edge->data);
        $this->assertSame($this->graph, $edge->graph);
    }

    public function testConnectFromNonExistingNodeThrowsException(): void
    {
        $node = $this->graph->addNode('node', 'data1');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node with ID "node-1" not found');

        $this->graph->connect('node-1', $node, 'connection data');
    }

    public function testConnectToNonExistingNodeThrowsException(): void
    {
        $node = $this->graph->addNode('node', 'data1');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node with ID "node-1" not found');

        $this->graph->connect($node, 'node-1', 'connection data');
    }

    public function testConnectWithNullData(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');

        $edge = $this->graph->connect($node1, $node2);

        $this->assertNull($edge->data);
    }

    public function testCanConnectNodeToItself(): void
    {
        $node = $this->graph->addNode('self-node', 'data');

        $edge = $this->graph->connect($node, $node, 'self-loop');

        $this->assertSame($node, $edge->from);
        $this->assertSame($node, $edge->to);
        $this->assertEquals('self-loop', $edge->data);
    }

    public function testCanCreateMultipleEdgesBetweenSameNodes(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');

        $edge1 = $this->graph->connect($node1, $node2, 'first');
        $edge2 = $this->graph->connect($node1, $node2, 'second');
        $edge3 = $this->graph->connect($node1, $node2, 'third');

        $outgoingEdges = $this->graph->getOutgoingEdges($node1);
        $incomingEdges = $this->graph->getIncomingEdges($node2);

        $this->assertCount(3, $outgoingEdges);
        $this->assertCount(3, $incomingEdges);
        $this->assertContains($edge1, $outgoingEdges);
        $this->assertContains($edge2, $outgoingEdges);
        $this->assertContains($edge3, $outgoingEdges);
    }

    public function testConnectingNodesFromNodeInDifferentGraphThrowsException(): void
    {
        $graph2 = new Graph();

        $from = $graph2->addNode('node-1', 'data1');
        $to = $this->graph->addNode('node-2', 'data2');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node "node-1" does not belong to this graph');

        $this->graph->connect($from, $to);
    }

    public function testConnectingNodesToNodeInDifferentGraphThrowsException(): void
    {
        $graph2 = new Graph();

        $from = $this->graph->addNode('node-1', 'data1');
        $to = $graph2->addNode('node-2', 'data2');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node "node-2" does not belong to this graph');

        $this->graph->connect($from, $to);
    }

    public function testGetOutgoingEdgesReturnsCorrectEdges(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');
        $node3 = $this->graph->addNode('node-3', 'data3');

        $edge1 = $this->graph->connect($node1, $node2, 'edge1');
        $edge2 = $this->graph->connect($node1, $node3, 'edge2');
        $this->graph->connect($node2, $node3, 'edge3'); // Should not be in node1's outgoing

        $outgoingEdges = $this->graph->getOutgoingEdges($node1);

        $this->assertCount(2, $outgoingEdges);
        $this->assertContains($edge1, $outgoingEdges);
        $this->assertContains($edge2, $outgoingEdges);
    }

    public function testGetIncomingEdgesReturnsCorrectEdges(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');
        $node3 = $this->graph->addNode('node-3', 'data3');

        $edge1 = $this->graph->connect($node1, $node3, 'edge1');
        $edge2 = $this->graph->connect($node2, $node3, 'edge2');
        $this->graph->connect($node1, $node2, 'edge3'); // Should not be in node3's incoming

        $incomingEdges = $this->graph->getIncomingEdges($node3);

        $this->assertCount(2, $incomingEdges);
        $this->assertContains($edge1, $incomingEdges);
        $this->assertContains($edge2, $incomingEdges);
    }

    public function testGetOutgoingEdgesForNodeWithNoEdges(): void
    {
        $node = $this->graph->addNode('isolated-node', 'data');

        $outgoingEdges = $this->graph->getOutgoingEdges($node);

        $this->assertEmpty($outgoingEdges);
    }

    public function testGetIncomingEdgesForNodeWithNoEdges(): void
    {
        $node = $this->graph->addNode('isolated-node', 'data');

        $incomingEdges = $this->graph->getIncomingEdges($node);

        $this->assertEmpty($incomingEdges);
    }

    public function testComplexGraphStructure(): void
    {
        // Create a more complex graph: A -> B -> C, A -> C, B -> A
        $nodeA = $this->graph->addNode('A', 'dataA');
        $nodeB = $this->graph->addNode('B', 'dataB');
        $nodeC = $this->graph->addNode('C', 'dataC');

        $edgeAB = $this->graph->connect($nodeA, $nodeB, 'A->B');
        $edgeBC = $this->graph->connect($nodeB, $nodeC, 'B->C');
        $edgeAC = $this->graph->connect($nodeA, $nodeC, 'A->C');
        $edgeBA = $this->graph->connect($nodeB, $nodeA, 'B->A');

        // Verify outgoing edges
        $this->assertCount(2, $this->graph->getOutgoingEdges($nodeA)); // A->B, A->C
        $this->assertCount(2, $this->graph->getOutgoingEdges($nodeB)); // B->C, B->A
        $this->assertCount(0, $this->graph->getOutgoingEdges($nodeC)); // No outgoing

        // Verify incoming edges
        $this->assertCount(1, $this->graph->getIncomingEdges($nodeA)); // B->A
        $this->assertCount(1, $this->graph->getIncomingEdges($nodeB)); // A->B
        $this->assertCount(2, $this->graph->getIncomingEdges($nodeC)); // B->C, A->C
    }

    public function testAddNodeInitializesEdgeArrays(): void
    {
        $node = $this->graph->addNode('test-node', 'data');

        // Should be able to get edges without errors
        $this->assertIsArray($this->graph->getOutgoingEdges($node));
        $this->assertIsArray($this->graph->getIncomingEdges($node));
        $this->assertEmpty($this->graph->getOutgoingEdges($node));
        $this->assertEmpty($this->graph->getIncomingEdges($node));
    }
}
