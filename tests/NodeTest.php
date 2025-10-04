<?php

declare(strict_types=1);

namespace Alcaeus\Graph\Tests;

use Alcaeus\Graph\Graph;
use Alcaeus\Graph\Node;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Node::class)]
class NodeTest extends TestCase
{
    private Graph $graph;

    protected function setUp(): void
    {
        $this->graph = new Graph();
    }

    public function testCanCreateNodeWithIdAndData(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $node = $this->graph->addNode('user-1', $data);

        $this->assertSame($this->graph, $node->graph);
        $this->assertEquals('user-1', $node->id);
        $this->assertSame($data, $node->data);
    }

    public function testCanCreateNodeWithStringData(): void
    {
        $node = $this->graph->addNode('node-1', 'simple string data');

        $this->assertEquals('node-1', $node->id);
        $this->assertEquals('simple string data', $node->data);
    }

    public function testCanCreateNodeWithNullData(): void
    {
        $node = $this->graph->addNode('node-1', null);

        $this->assertEquals('node-1', $node->id);
        $this->assertNull($node->data);
    }

    public function testCanCreateNodeWithoutData(): void
    {
        $node = $this->graph->addNode('node-1');

        $this->assertEquals('node-1', $node->id);
        $this->assertNull($node->data);
    }

    public function testCanCreateNodeWithObjectData(): void
    {
        $data = new stdClass();
        $data->property = 'value';
        $node = $this->graph->addNode('node-1', $data);

        $this->assertEquals('node-1', $node->id);
        $this->assertSame($data, $node->data);
    }

    public function testIncomingEdgesInitiallyEmpty(): void
    {
        $node = $this->graph->addNode('node-1', 'data');

        $this->assertEmpty($node->incomingEdges);
    }

    public function testOutgoingEdgesInitiallyEmpty(): void
    {
        $node = $this->graph->addNode('node-1', 'data');

        $this->assertEmpty($node->outgoingEdges);
    }

    public function testIncomingEdgesReflectConnections(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');

        $edge = $this->graph->connect($node1, $node2);

        $this->assertEmpty($node1->incomingEdges);
        $this->assertCount(1, $node2->incomingEdges);
        $this->assertSame($edge, $node2->incomingEdges[0]);
    }

    public function testOutgoingEdgesReflectConnections(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');

        $edge = $this->graph->connect($node1, $node2);

        $this->assertCount(1, $node1->outgoingEdges);
        $this->assertEmpty($node2->outgoingEdges);
        $this->assertSame($edge, $node1->outgoingEdges[0]);
    }

    public function testConnectMethodCreatesEdge(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');

        $edge = $node1->connect($node2, 'edge data');

        $this->assertSame($node1, $edge->from);
        $this->assertSame($node2, $edge->to);
        $this->assertEquals('edge data', $edge->data);
    }

    public function testConnectMethodWithNullData(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');

        $edge = $node1->connect($node2);

        $this->assertSame($node1, $edge->from);
        $this->assertSame($node2, $edge->to);
        $this->assertNull($edge->data);
    }

    public function testConnectToSelfIsAllowed(): void
    {
        $node = $this->graph->addNode('node-1', 'data');

        $edge = $node->connect($node, 'self-reference');

        $this->assertSame($node, $edge->from);
        $this->assertSame($node, $edge->to);
        $this->assertEquals('self-reference', $edge->data);
    }

    public function testMultipleEdgesBetweenSameNodes(): void
    {
        $node1 = $this->graph->addNode('node-1', 'data1');
        $node2 = $this->graph->addNode('node-2', 'data2');

        $edge1 = $node1->connect($node2, 'first edge');
        $edge2 = $node1->connect($node2, 'second edge');

        $this->assertCount(2, $node1->outgoingEdges);
        $this->assertCount(2, $node2->incomingEdges);
        $this->assertNotSame($edge1, $edge2);
        $this->assertEquals('first edge', $edge1->data);
        $this->assertEquals('second edge', $edge2->data);
    }

    public function testConnectingNodeFromDifferentGraphThrowsException(): void
    {
        $graph1 = new Graph();
        $graph2 = new Graph();

        $node1 = $graph1->addNode('node-1', 'data1');
        $node2 = $graph2->addNode('node-2', 'data2');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node "node-2" does not belong to this graph');

        $node1->connect($node2);
    }
}
