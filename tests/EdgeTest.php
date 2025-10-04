<?php

declare(strict_types=1);

namespace Alcaeus\Graph\Tests;

use Alcaeus\Graph\Edge;
use Alcaeus\Graph\Graph;
use Alcaeus\Graph\Node;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

#[CoversClass(Edge::class)]
class EdgeTest extends TestCase
{
    private Graph $graph;
    private Node $node1;
    private Node $node2;

    protected function setUp(): void
    {
        $this->graph = new Graph();
        $this->node1 = $this->graph->addNode('node-1', 'data1');
        $this->node2 = $this->graph->addNode('node-2', 'data2');
    }

    public function testCanCreateEdgeWithData(): void
    {
        $edgeData = ['weight' => 5, 'type' => 'connection'];
        $edge = new Edge($this->node1, $this->node2, $edgeData);

        $this->assertSame($this->node1, $edge->from);
        $this->assertSame($this->node2, $edge->to);
        $this->assertSame($edgeData, $edge->data);
    }

    public function testCanCreateEdgeWithNullData(): void
    {
        $edge = new Edge($this->node1, $this->node2, null);

        $this->assertSame($this->node1, $edge->from);
        $this->assertSame($this->node2, $edge->to);
        $this->assertNull($edge->data);
    }

    public function testCanCreateEdgeWithoutData(): void
    {
        $edge = new Edge($this->node1, $this->node2);

        $this->assertSame($this->node1, $edge->from);
        $this->assertSame($this->node2, $edge->to);
        $this->assertNull($edge->data);
    }

    public function testCanCreateEdgeWithStringData(): void
    {
        $edge = new Edge($this->node1, $this->node2, 'string data');

        $this->assertEquals('string data', $edge->data);
    }

    public function testCanCreateEdgeWithNumericData(): void
    {
        $edge = new Edge($this->node1, $this->node2, 42);

        $this->assertEquals(42, $edge->data);
    }

    public function testCanCreateEdgeWithObjectData(): void
    {
        $data = new stdClass();
        $data->property = 'value';
        $edge = new Edge($this->node1, $this->node2, $data);

        $this->assertSame($data, $edge->data);
    }

    public function testEdgeGraphPropertyReturnsFromNodeGraph(): void
    {
        $edge = new Edge($this->node1, $this->node2, 'data');

        $this->assertSame($this->graph, $edge->graph);
        $this->assertSame($this->node1->graph, $edge->graph);
    }

    public function testCanCreateSelfLoop(): void
    {
        $edge = new Edge($this->node1, $this->node1, 'self-loop');

        $this->assertSame($this->node1, $edge->from);
        $this->assertSame($this->node1, $edge->to);
        $this->assertEquals('self-loop', $edge->data);
    }

    public function testEdgeWithDifferentDataTypes(): void
    {
        $arrayData = ['key' => 'value', 'number' => 123];
        $edge1 = new Edge($this->node1, $this->node2, $arrayData);

        $boolData = true;
        $edge2 = new Edge($this->node1, $this->node2, $boolData);

        $floatData = 3.14159;
        $edge3 = new Edge($this->node1, $this->node2, $floatData);

        $this->assertSame($arrayData, $edge1->data);
        $this->assertSame($boolData, $edge2->data);
        $this->assertSame($floatData, $edge3->data);
    }

    public function testMultipleEdgesBetweenSameNodesAreDistinct(): void
    {
        $edge1 = new Edge($this->node1, $this->node2, 'first');
        $edge2 = new Edge($this->node1, $this->node2, 'second');

        $this->assertNotSame($edge1, $edge2);
        $this->assertSame($this->node1, $edge1->from);
        $this->assertSame($this->node1, $edge2->from);
        $this->assertSame($this->node2, $edge1->to);
        $this->assertSame($this->node2, $edge2->to);
        $this->assertEquals('first', $edge1->data);
        $this->assertEquals('second', $edge2->data);
    }

    public function testEdgePropertiesAreReadonly(): void
    {
        $edge = new Edge($this->node1, $this->node2, 'data');

        // These should be readonly properties - test that they exist and are accessible
        $this->assertSame($this->node1, $edge->from);
        $this->assertSame($this->node2, $edge->to);
        $this->assertEquals('data', $edge->data);

        // Properties should be readonly (this is enforced by PHP's readonly keyword)
        $reflection = new ReflectionClass($edge);
        $fromProperty = $reflection->getProperty('from');
        $toProperty = $reflection->getProperty('to');
        $dataProperty = $reflection->getProperty('data');

        $this->assertTrue($fromProperty->isReadOnly());
        $this->assertTrue($toProperty->isReadOnly());
        $this->assertTrue($dataProperty->isReadOnly());
    }

    public function testEdgeGraphPropertyIsVirtual(): void
    {
        $edge = new Edge($this->node1, $this->node2, 'data');

        // The graph property should be computed from the from node
        $this->assertSame($this->node1->graph, $edge->graph);

        // Test with nodes from different graphs
        $otherGraph = new Graph();
        $otherNode = $otherGraph->addNode('other-node', 'other-data');

        $edgeWithOtherNode = new Edge($otherNode, $this->node2, 'cross-graph');
        $this->assertSame($otherGraph, $edgeWithOtherNode->graph);
        $this->assertNotSame($this->graph, $edgeWithOtherNode->graph);
    }

    public function testEdgeWithComplexNestedData(): void
    {
        $complexData = [
            'metadata' => [
                'created' => new DateTime(),
                'author' => 'system',
                'tags' => ['important', 'automated'],
            ],
            'properties' => [
                'weight' => 0.75,
                'bidirectional' => false,
                'attributes' => [
                    'color' => 'blue',
                    'thickness' => 2,
                ],
            ],
        ];

        $edge = new Edge($this->node1, $this->node2, $complexData);

        $this->assertSame($complexData, $edge->data);
        $this->assertInstanceOf(DateTime::class, $edge->data['metadata']['created']);
        $this->assertEquals('system', $edge->data['metadata']['author']);
        $this->assertEquals(0.75, $edge->data['properties']['weight']);
    }

    public function testEdgeClassIsFinal(): void
    {
        $reflection = new ReflectionClass(Edge::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testEdgeWithCallableData(): void
    {
        $callable = static fn () => 'callable result';
        $edge = new Edge($this->node1, $this->node2, $callable);

        $this->assertSame($callable, $edge->data);
        $this->assertEquals('callable result', ($edge->data)());
    }
}
