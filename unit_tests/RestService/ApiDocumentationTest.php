<?php

namespace Cougar\UnitTests\RestService;

use Cougar\RestService\ApiDocumentation;

class ApiDocumentationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The object we are testing
     *
     * @var \Cougar\RestService\ApiDocumentation
     */
    protected $object;

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../../cougar.php");
    }

    /**
     * Sets up the object with the test interfaces
     *
     * @covers \Cougar\RestService\DocumentationBuilder::__constructor
     * @covers \Cougar\RestService\DocumentationBuilder::addComponent
     * @covers \Cougar\RestService\DocumentationBuilder::parseMethodAnnotations
     * @covers \Cougar\RestService\DocumentationBuilder::extractResource
     * @covers \Cougar\RestService\DocumentationBuilder::extractAction
     */
    protected function setUp()
    {
        $this->object = new ApiDocumentation("http://localhost/unit-tests",
            "Unit Tests", "API Documentation Unit Tests");
        $this->object->addComponent("ComponentX", "Component X",
            __NAMESPACE__ . "\\iComponentX");
        $this->object->addComponent("ComponentY", "Component Y",
            __NAMESPACE__ . "\\iComponentY", "Provides lists of things");
    }

    /**
     * @covers \Cougar\RestService\DocumentationBuilder::getApplicationInformation
     */
    public function testGetApplicationInformation()
    {
        $app_info = $this->object->getApplicationInformation();
        $this->assertEquals("http://localhost/unit-tests",
            $app_info->urlPrefix);
        $this->assertEquals("Unit Tests", $app_info->name);
        $this->assertEquals("API Documentation Unit Tests",
            $app_info->description);
    }

    /**
     * @covers \Cougar\RestService\DocumentationBuilder::getComponents
     */
    public function testGetComponents()
    {
        $components = $this->object->getComponents();
        $this->assertCount(2, $components);
        $this->assertContainsOnlyInstancesOf(
            '\Cougar\RestService\Models\Component', $components);

        $this->assertEquals("ComponentX", $components[0]->componentId);
        $this->assertEquals("Component X", $components[0]->name);
        $this->assertEquals("", $components[0]->description);

        $this->assertEquals("ComponentY", $components[1]->componentId);
        $this->assertEquals("Component Y", $components[1]->name);
        $this->assertEquals("Provides lists of things",
            $components[1]->description);

        // Convert component to an array and make resources are not included
        $component = $components[0]->__toArray();
        $this->assertArrayNotHasKey("resources", $component);
    }

    /**
     * @covers \Cougar\RestService\DocumentationBuilder::getResources
     */
    public function testGetResourcesComponentX()
    {
        $resources = $this->object->getResources("ComponentX");
        $this->assertCount(1, $resources);

        $this->assertEquals("Cougar.UnitTests.RestService.Stuff",
            $resources[0]->resourceId);
        $this->assertEquals("Cougar.UnitTests.RestService.Stuff",
            $resources[0]->name);
        $this->assertEquals('\Cougar\UnitTests\RestService\Stuff',
            $resources[0]->class);
        $this->assertEquals('Stuff structure', $resources[0]->shortDescription);
        $this->assertCount(5, $resources[0]->actions);

        // Convert resource to an array and make sure actions are not included
        $resource = $resources[0]->__toArray();
        $this->assertArrayNotHasKey("actions", $resource);
        $this->assertArrayNotHasKey("class", $resource);
    }

    /**
     * @covers \Cougar\RestService\DocumentationBuilder::getResources
     */
    public function testGetResourcesComponentY()
    {
        $resources = $this->object->getResources("ComponentY");
        $this->assertCount(1, $resources);

        $this->assertEquals("Cougar.UnitTests.RestService.Thing",
            $resources[0]->resourceId);
        $this->assertEquals("Cougar.UnitTests.RestService.Thing",
            $resources[0]->name);
        $this->assertEquals('\Cougar\UnitTests\RestService\Thing',
            $resources[0]->class);
        $this->assertEquals('A thing (hopefully great)',
            $resources[0]->shortDescription);
        $this->assertCount(2, $resources[0]->actions);

        // Convert resource to an array and make sure actions are not included
        $resource = $resources[0]->__toArray();
        $this->assertArrayNotHasKey("actions", $resource);
        $this->assertArrayNotHasKey("class", $resource);
    }

    /**
     * @covers \Cougar\RestService\DocumentationBuilder::getResourceActions
     */
    public function testGetResourceActions()
    {
        $actions = $this->object->getResourceActions(
            "Cougar.UnitTests.RestService.Stuff");
        $this->assertCount(5, $actions);
        $this->assertEquals("list", $actions[0]->actionId);
        $this->assertEquals("read", $actions[1]->actionId);
        $this->assertEquals("create", $actions[2]->actionId);
        $this->assertEquals("update", $actions[3]->actionId);
        $this->assertEquals("delete", $actions[4]->actionId);

        $this->assertEquals("none", $actions[0]->authentication);
        $this->assertEquals("none", $actions[1]->authentication);
        $this->assertEquals("required", $actions[2]->authentication);
        $this->assertEquals("required", $actions[3]->authentication);
        $this->assertEquals("required", $actions[4]->authentication);

        // Convert action to an array and make sure details are not included
        $action = $actions[0]->__toArray();
        $this->assertArrayNotHasKey("parameters", $action);
        $this->assertArrayNotHasKey("description", $action);
    }

    /**
     * @covers \Cougar\RestService\DocumentationBuilder::getResourceActions
     */
    public function testGetResourceActionsForThings()
    {
        $actions = $this->object->getResourceActions(
            "Cougar.UnitTests.RestService.Thing");
        $this->assertCount(2, $actions);
        $this->assertEquals("list", $actions[0]->actionId);
        $this->assertEquals("list.1", $actions[1]->actionId);

        $this->assertEquals("optional", $actions[0]->authentication);
        $this->assertEquals("none", $actions[1]->authentication);

        // Convert action to an array and make sure details are not included
        $action = $actions[0]->__toArray();
        $this->assertArrayNotHasKey("parameters", $action);
        $this->assertArrayNotHasKey("description", $action);
    }

    /**
     * @covers \Cougar\RestService\DocumentationBuilder::getResourceAction
     */
    public function testGetResourceAction()
    {
        $action = $this->object->getResourceAction(
            "Cougar.UnitTests.RestService.Stuff", "read");
        $this->assertEquals("read", $action->actionId);
        $this->assertEquals("read", $action->name);
        $this->assertEquals("Returns stuff", $action->shortDescription);
        $this->assertEquals("Returns stuff", $action->description);
        $this->assertEquals(array("GET"), $action->httpMethods);
        $this->assertEquals(array("/path/to/stuff/{stuff_id}"), $action->paths);

        $this->assertCount(1, $action->parameters);
        $this->assertEquals("stuff_id", $action->parameters[0]->name);
        $this->assertEquals("URI", $action->parameters[0]->location);
        $this->assertEquals("int", $action->parameters[0]->type);
        $this->assertFalse($action->parameters[0]->list);
        $this->assertNull($action->parameters[0]->constraint);
        $this->assertEquals("ID of the stuff you want to get",
            $action->parameters[0]->description);

        $this->assertEquals('Cougar.UnitTests.RestService.Stuff',
            $action->returnValue->type);
        $this->assertFalse($action->returnValue->list);
        $this->assertEquals("Requested Stuff record",
            $action->returnValue->description);
    }

    /**
     * @covers \Cougar\RestService\DocumentationBuilder::getResource
     */
    public function testGetResourceWithStuff()
    {
        $resource = $this->object->getResource(
            "Cougar.UnitTests.RestService.Stuff");

        $this->assertEquals("Cougar.UnitTests.RestService.Stuff",
            $resource->resourceId);
        $this->assertEquals("Cougar.UnitTests.RestService.Stuff",
            $resource->name);
        $this->assertEquals("Stuff structure", $resource->description);
        $this->assertCount(3, $resource->values);

        $this->assertEquals("stuffId", $resource->values[0]->name);
        $this->assertEquals("int", $resource->values[0]->type);
        $this->assertFalse($resource->values[0]->list);
        $this->assertFalse($resource->values[0]->isResource);
        $this->assertFalse($resource->values[0]->optional);
        $this->assertEquals("Stuff's ID", $resource->values[0]->description);

        $this->assertEquals("name", $resource->values[1]->name);
        $this->assertEquals("string", $resource->values[1]->type);
        $this->assertFalse($resource->values[1]->list);
        $this->assertFalse($resource->values[1]->isResource);
        $this->assertTrue($resource->values[1]->optional);
        $this->assertEquals("This name given to this stuff",
            $resource->values[1]->description);

        $this->assertEquals("things", $resource->values[2]->name);
        $this->assertEquals("Cougar.UnitTests.RestService.Thing",
            $resource->values[2]->type);
        $this->assertTrue($resource->values[2]->list);
        $this->assertTrue($resource->values[2]->isResource);
        $this->assertFalse($resource->values[2]->optional);
        $this->assertEquals("List of things",
            $resource->values[2]->description);
    }

    /**
     * @covers \Cougar\RestService\DocumentationBuilder::getResource
     */
    public function testGetResourceWithThing()
    {
        $resource = $this->object->getResource(
            "Cougar.UnitTests.RestService.Thing");

        $this->assertEquals("Cougar.UnitTests.RestService.Thing",
            $resource->resourceId);
        $this->assertEquals("Cougar.UnitTests.RestService.Thing",
            $resource->name);
        $this->assertEquals("A thing (hopefully great). Second sentence.",
            $resource->description);
        $this->assertCount(2, $resource->values);

        $this->assertEquals("thingId", $resource->values[0]->name);
        $this->assertEquals("int", $resource->values[0]->type);
        $this->assertFalse($resource->values[0]->list);
        $this->assertFalse($resource->values[0]->isResource);
        $this->assertFalse($resource->values[0]->optional);
        $this->assertEquals("ID of The Thing",
            $resource->values[0]->description);

        $this->assertEquals("created", $resource->values[1]->name);
        $this->assertEquals("DateTime", $resource->values[1]->type);
        $this->assertFalse($resource->values[1]->list);
        $this->assertFalse($resource->values[1]->isResource);
        $this->assertFalse($resource->values[1]->optional);
        $this->assertEquals("When The Thing was created",
            $resource->values[1]->description);
    }
}


/**
 * This is Component X. It is for testing purposes only. The unit tests will
 * extract information from this class and its methods to determine that the API
 * Documentation Builder is working properly.
 *
 * Let's hope this works.
 */
interface iComponentX
{
    /**
     * Returns a list of stuff objects. You may specify a query to return only
     * certain objects.
     *
     * @Path /path/to/stuff
     * @Methods GET
     * @GetQuery query
     *
     * @param array $parameters Query parameters
     * @return \Cougar\UnitTests\RestService\Stuff[] List of stuff
     */
    public function getListOfStuff(array $parameters = array());

    /**
     * Returns stuff
     *
     * @Path /path/to/stuff/:stuff_id
     * @Methods GET
     *
     * @param int $stuff_id
     *   ID of the stuff you want to get
     * @return \Cougar\UnitTests\RestService\Stuff Requested Stuff record
     */
    public function getStuff($stuff_id);

    /**
     * Creates new stuff.
     *
     * @Path /path/to/stuff
     * @Methods POST
     * @Body object stuff
     * @Authentication required
     *
     * @param \Cougar\UnitTests\RestService\Stuff $stuff
     *   Stuff to create
     * @return \Cougar\UnitTests\RestService\Stuff Created Stuff record
     */
    public function createStuff($stuff);

    /**
     * Updates existing stuff.
     *
     * @Path /path/to/stuff/:stuff_id
     * @Methods PUT
     * @Body object stuff
     * @Authentication required
     *
     * @param int $stuff_id
     *   ID of the stuff you want to update
     * @param \Cougar\UnitTests\RestService\Stuff $stuff
     *   Stuff to create
     * @return \Cougar\UnitTests\RestService\Stuff Created Stuff record
     */
    public function updateStuff($stuff_id, $stuff);

    /**
     * Deletes stuff. Of course, you have to specify which stuff to delete.
     *
     * @Path /path/to/stuff/:stuff_id
     * @Methods DELETE
     * @Resource \Cougar\UnitTests\RestService\Stuff
     * @Authentication required
     *
     * @param int $stuff_id
     *   ID of the stuff you want to delete
     */
    public function deleteStuff($stuff_id);
}

/**
 * This is Component Y, another component in the application.
 */
interface iComponentY
{
    /**
     * Gets a list of things that are still pending.
     *
     * @Path /path/to/things/pending
     * @Methods GET
     * @Authentication optional
     *
     * @return \Cougar\UnitTests\RestService\Thing[] List of pending things
     */
    public function getPendingThings();

    /**
     * Gets a list of things that have been completed.
     *
     * @Path /path/to/things/completed
     * @Methods GET
     *
     * @return \Cougar\UnitTests\RestService\Thing[] List of pending things
     */
    public function getCompletedThings();
}

/**
 * Stuff structure
 */
class Stuff
{
    /**
     * @var int Stuff's ID
     */
    public $stuffId;

    /**
     * This name given to this stuff
     *
     * @View __default__ optional
     * @var string
     */
    public $name;

    /**
     * @var \Cougar\UnitTests\RestService\Thing[] List of things
     */
    public $things;
}

/**
 * A thing (hopefully great). Second sentence.
 */
class Thing
{
    /**
     * @var int ID of The Thing
     */
    public $thingId;

    /**
     * @var DateTime When The Thing was created
     */
    public $created;
}
?>
