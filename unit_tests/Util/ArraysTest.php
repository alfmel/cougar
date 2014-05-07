<?php

namespace Cougar\UnitTests\Util;

use stdClass;
use Cougar\Util\Arrays;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-06-13 at 15:17:30.
 */
class ArraysTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * @covers \Cougar\Util\Arrays::toAssociative
     */
    public function testToAssociative() {
        # Define the array
        $array = array(
            array(
                "id" => "id_1",
                "value" => "Value 1"
            ),
            array(
                "id" => "id_2",
                "value" => "Value 2"
            ),
            array(
                "id" => "id_3",
                "value" => "Value 3"
            ),
            array(
                "id" => "id_4",
                "value" => "Value 4"
            ),
            array(
                "id" => "id_5",
                "value" => "Value 5"
            )
        );
        
        # Convert the array
        $assoc_array = Arrays::toAssociative($array, "id");
        
        $this->assertCount(5, $assoc_array);
        foreach($assoc_array as $key => $value)
        {
            $this->assertEquals($key, $value["id"]);
        }
    }
    
    /**
     * @covers \Cougar\Util\Arrays::toAssociative
     */
    public function testToAssociativeWithObjects() {
        # Define the objects
        $obj1 = new \stdClass();
        $obj1->id = "id_1";
        $obj1->value = "Value 1";
        
        $obj2 = new \stdClass();
        $obj2->id = "id_2";
        $obj2->value = "Value 2";
        
        $obj3 = new \stdClass();
        $obj3->id = "id_3";
        $obj3->value = "Value 3";
        
        $obj4 = new \stdClass();
        $obj4->id = "id_4";
        $obj4->value = "Value 4";
        
        $obj5 = new \stdClass();
        $obj5->id = "id_5";
        $obj5->value = "Value 5";
        
        $array = array($obj1, $obj2, $obj3, $obj4, $obj5);
        
        # Convert the array
        $assoc_array = Arrays::toAssociative($array, "id");
        
        $this->assertCount(5, $assoc_array);
        foreach($assoc_array as $key => $value)
        {
            $this->assertEquals($key, $value->id);
        }
    }

    /**
     * @covers \Cougar\Util\Arrays::renameKeys
     */
    public function testRenameKeys()
    {
        // Define an array to test
        $array = array(
            array(
                "record_id" => 1,
                "first_name" => "Peter",
                "LAST_NAME" => "Stevens",
                "age" => 45
            ),
            array(
                "record_id" => 2,
                "first_name" => "John",
                "LAST_NAME" => "Stevens",
                "age" => 45
            ),
            array(
                "record_id" => 3,
                "first_name" => "John",
                "LAST_NAME" => "Smith",
                "age" => 45
            ),
            array(
                "record_id" => 4,
                "first_name" => "Mark",
                "LAST_NAME" => "Johnson",
                "age" => 58
            ),
            array(
                "record_id" => 5,
                "first_name" => "Michael",
                "LAST_NAME" => "Zimmerman",
                "age" => 19
            )
        );

        // Define the key map
        $key_map = array(
            "record_id" => "id",
            "first_name" => "firstName",
            "LAST_NAME" => "lastName",
            "age" => "age"
        );

        // Define the array we expect to receive
        $expected_array = array(
            array(
                "id" => 1,
                "firstName" => "Peter",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 2,
                "firstName" => "John",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 3,
                "firstName" => "John",
                "lastName" => "Smith",
                "age" => 45
            ),
            array(
                "id" => 4,
                "firstName" => "Mark",
                "lastName" => "Johnson",
                "age" => 58
            ),
            array(
                "id" => 5,
                "firstName" => "Michael",
                "lastName" => "Zimmerman",
                "age" => 19
            )
        );

        // Rename the keys
        $modified_array = Arrays::renameKeys($array, $key_map);

        // Make sure the arrays match
        $this->assertEquals($expected_array, $modified_array);
    }

    /**
     * @covers \Cougar\Util\Arrays::renameKeys
     * @depends testRenameKeys
     */
    public function testRenameKeysPerformance()
    {
        // Create an array with 50,000 elements
        $n = 50000;

        $array = array();
        for ($i = 0; $i < $n; $i++)
        {
            $array[] = array(
                "record_id" => 1,
                "first_name" => "Peter",
                "LAST_NAME" => "Stevens",
                "age" => 45
            );
        }

        // Define the key map
        $key_map = array(
            "record_id" => "id",
            "first_name" => "firstName",
            "LAST_NAME" => "lastName",
            "age" => "age"
        );

        // Start the timer
        $start_time = microtime(true);

        // Rename the keys
        $new_data = Arrays::renameKeys($array, $key_map);

        // Stop the timer
        $stop_time = microtime(true);

        // Make sure the rename took less than one second
        $this->assertLessThan(1, $stop_time - $start_time);
    }

    /**
     * @covers \Cougar\Util\Arrays::dataSort
     */
    public function testDataSort() {
        $array = array(
            array(
                "id" => 1,
                "firstName" => "Peter",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 2,
                "firstName" => "John",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 3,
                "firstName" => "John",
                "lastName" => "Smith",
                "age" => 45
            ),
            array(
                "id" => 4,
                "firstName" => "Mark",
                "lastName" => "Johnson",
                "age" => 58
            ),
            array(
                "id" => 5,
                "firstName" => "Michael",
                "lastName" => "Zimmerman",
                "age" => 19
            )
        );
        
        $sorted_array = Arrays::dataSort(
            $array, "age", "lastName", "firstName");
        
        $this->assertCount(5, $sorted_array);
        $this->assertEquals(5, $sorted_array[0]["id"]);
        $this->assertEquals(3, $sorted_array[1]["id"]);
        $this->assertEquals(2, $sorted_array[2]["id"]);
        $this->assertEquals(1, $sorted_array[3]["id"]);
        $this->assertEquals(4, $sorted_array[4]["id"]);
    }

    /**
     * @covers \Cougar\Util\Arrays::dataFilter
     */
    public function testDataFilterSingleValue()
    {
        // Define the array with records
        $records = array(
            array(
                "id" => 1,
                "firstName" => "Peter",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 2,
                "firstName" => "John",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 3,
                "firstName" => "John",
                "lastName" => "Smith",
                "age" => 45
            ),
            array(
                "id" => 4,
                "firstName" => "Mark",
                "lastName" => "Johnson",
                "age" => 58
            ),
            array(
                "id" => 5,
                "firstName" => "Michael",
                "lastName" => "Zimmerman",
                "age" => 19
            )
        );

        // Filter the results by last name
        $filtered_records = Arrays::dataFilter($records, "lastName", "Smith");
        $this->assertCount(1, $filtered_records);
        $this->assertEquals($filtered_records[2]["lastName"], "Smith");
    }

    /**
     * @covers \Cougar\Util\Arrays::dataFilter
     */
    public function testDataFilterSingleValueNegate()
    {
        // Define the array with records
        $records = array(
            array(
                "id" => 1,
                "firstName" => "Peter",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 2,
                "firstName" => "John",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 3,
                "firstName" => "John",
                "lastName" => "Smith",
                "age" => 45
            ),
            array(
                "id" => 4,
                "firstName" => "Mark",
                "lastName" => "Johnson",
                "age" => 58
            ),
            array(
                "id" => 5,
                "firstName" => "Michael",
                "lastName" => "Zimmerman",
                "age" => 19
            )
        );

        // Filter the results by last name
        $filtered_records = Arrays::dataFilter($records, "lastName", "Smith",
            false);
        $this->assertCount(4, $filtered_records);
        foreach($filtered_records as $record)
        {
            $this->assertNotEquals($record["lastName"], "Smith");
        }
    }

    /**
     * @covers \Cougar\Util\Arrays::dataFilter
     */
    public function testDataFilterMultipleValues()
    {
        // Define the array with records
        $records = array(
            array(
                "id" => 1,
                "firstName" => "Peter",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 2,
                "firstName" => "John",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 3,
                "firstName" => "John",
                "lastName" => "Smith",
                "age" => 45
            ),
            array(
                "id" => 4,
                "firstName" => "Mark",
                "lastName" => "Johnson",
                "age" => 58
            ),
            array(
                "id" => 5,
                "firstName" => "Michael",
                "lastName" => "Zimmerman",
                "age" => 19
            )
        );

        // Filter the results by last name
        $filtered_records = Arrays::dataFilter($records, "firstName",
            array("John", "Michael"));
        $this->assertCount(3, $filtered_records);
    }

    /**
     * @covers \Cougar\Util\Arrays::dataFilter
     */
    public function testDataFilterMultipleValuesNegate()
    {
        // Define the array with records
        $records = array(
            array(
                "id" => 1,
                "firstName" => "Peter",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 2,
                "firstName" => "John",
                "lastName" => "Stevens",
                "age" => 45
            ),
            array(
                "id" => 3,
                "firstName" => "John",
                "lastName" => "Smith",
                "age" => 45
            ),
            array(
                "id" => 4,
                "firstName" => "Mark",
                "lastName" => "Johnson",
                "age" => 58
            ),
            array(
                "id" => 5,
                "firstName" => "Michael",
                "lastName" => "Zimmerman",
                "age" => 19
            )
        );

        // Filter the results by last name
        $filtered_records = Arrays::dataFilter($records, "firstName",
            array("John", "Michael"), false);
        $this->assertCount(2, $filtered_records);
    }

    /**
     * @covers \Cougar\Util\Arrays::setModelView
     */
    public function testSetModelView()
    {
        $object1 = $this->getMockBuilder('\Cougar\Model\Model')
            ->disableOriginalConstructor()
            ->getMock();
        $object1->expects($this->once())
            ->method("__setView")
            ->with("new_view");

        $object2 = $this->getMockBuilder('\Cougar\Model\Model')
            ->disableOriginalConstructor()
            ->getMock();
        $object2->expects($this->once())
            ->method("__setView")
            ->with("new_view");

        $objects = array($object1, $object2);

        Arrays::setModelView($objects, "new_view");
    }

    /**
     * @covers \Cougar\Util\Arrays::setModelView
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testSetModelViewBadObjects()
    {
        $object1 = new stdClass();
        $object2 = new stdClass();

        $objects = array($object1, $object2);

        Arrays::setModelView($objects, "new_view");
    }

    /**
     * @covers \Cougar\Util\Arrays::cloneObjects
     */
    public function testCloneObjects()
    {
        $object1 = new stdClass();
        $object1->value = "Object 1";

        $object2 = new stdClass();
        $object2->value = "Object 2";

        $objects = array($object1, $object2);

        Arrays::cloneObjects($objects);

        $objects[0]->value = "Cloned Object 1";
        $objects[1]->value = "Cloned Object 2";

        $this->assertNotEquals($object1->value, $objects[0]->value);
        $this->assertNotEquals($object2->value, $objects[1]->value);
    }

    /**
     * @covers \Cougar\Util\Arrays::cloneObjects
     */
    public function testCloneObjectsMixedArray()
    {
        $object1 = new stdClass();
        $object1->value = "Object 1";

        $object2 = new stdClass();
        $object2->value = "Object 2";

        $objects = array($object1, $object2, "stuff");

        Arrays::cloneObjects($objects);

        $objects[0]->value = "Cloned Object 1";
        $objects[1]->value = "Cloned Object 2";

        $this->assertNotEquals($object1->value, $objects[0]->value);
        $this->assertNotEquals($object2->value, $objects[1]->value);
        $this->assertEquals("stuff", $objects[2]);
    }

    /**
     * @covers \Cougar\Util\Arrays::cloneObjectArray
     */
    public function testCloneObjectArray()
    {
        $object1 = new stdClass();
        $object1->value = "Object 1";

        $object2 = new stdClass();
        $object2->value = "Object 2";

        $objects = array($object1, $object2);

        $cloned_objects = Arrays::cloneObjectArray($objects);

        $cloned_objects[0]->value = "Cloned Object 1";
        $cloned_objects[1]->value = "Cloned Object 2";

        $this->assertNotEquals($object1->value, $cloned_objects[0]->value);
        $this->assertNotEquals($object2->value, $cloned_objects[1]->value);

        $this->assertEquals($object1->value, $objects[0]->value);
        $this->assertEquals($object2->value, $objects[1]->value);
    }

    /**
     * @covers \Cougar\Util\Arrays::cloneObjectArray
     */
    public function testCloneObjectArrayMixedArray()
    {
        $object1 = new stdClass();
        $object1->value = "Object 1";

        $object2 = new stdClass();
        $object2->value = "Object 2";

        $objects = array($object1, $object2, "stuff");

        $cloned_objects = Arrays::cloneObjectArray($objects);

        $cloned_objects[0]->value = "Cloned Object 1";
        $cloned_objects[1]->value = "Cloned Object 2";

        $this->assertNotEquals($object1->value, $cloned_objects[0]->value);
        $this->assertNotEquals($object2->value, $cloned_objects[1]->value);
        $this->assertEquals("stuff", $objects[2]);

        $this->assertEquals($object1->value, $objects[0]->value);
        $this->assertEquals($object2->value, $objects[1]->value);
    }
}
?>
