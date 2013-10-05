<?php

namespace Cougar\UnitTests\Util;

use SimpleXMLElement;
use Cougar\Util\Xml;

require_once(__DIR__ . "/../../../cougar.php");

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-04-12 at 10:55:50.
 */
class XmlTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers \Cougar\Util\Xml::toXml
     */
    public function testToXmlWithString() {
        $greeting = "Hello World!";
        
        $expected_xml =
            new SimpleXMLElement("<response/>");
        $expected_xml[0] = $greeting;
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($greeting)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     */
    public function testToXmlWithStringAndRootElement() {
        $greeting = "Hello World!";
        $root_element = "greeting";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        $expected_xml[0] = $greeting;
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($greeting, $root_element)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     */
    public function testToXmlWithNumber() {
        $number = 3.14;
        
        $expected_xml =
            new SimpleXMLElement("<response/>");
        $expected_xml[0] = $number;
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($number)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     */
    public function testToXmlWithNumberAndRootElement() {
        $number = 3.14;
        $root_element = "result";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        $expected_xml[0] = $number;
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($number, $root_element)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     */
    public function testToXmlWithBoolean() {
        $expected_xml =
            new SimpleXMLElement("<response/>");
        $expected_xml[0] = "true";
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml(true)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     */
    public function testToXmlWithBooleanAndRootElement() {
        $root_element = "result";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        $expected_xml[0] = "true";
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml(true, $root_element)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     * @covers \Cougar\Util\Xml::arrayIsAssociative
     */
    public function testToXmlWithNonAssociativeArray() {
        $array = array("a", "b", "c", "d", "e", false);
        $root_element = "response";
        $child_element = "object";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        foreach($array as $index => $value)
        {
            if ($index == 5)
            {
                $expected_xml->addChild($child_element, "false");
            }
            else
            {
                $expected_xml->addChild($child_element, $value);
            }
        }
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($array)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     * @covers \Cougar\Util\Xml::arrayIsAssociative
     */
    public function testToXmlWithNonAssociativeArrayAndNamedElements() {
        $array = array("a", "b", "c", "d", "e", false);
        $root_element = "alphabet";
        $child_element = "letter";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        foreach($array as $index => $value)
        {
            if ($index == 5)
            {
                $expected_xml->addChild($child_element, "false");
            }
            else
            {
                $expected_xml->addChild($child_element, $value);
            }
        }
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($array, $root_element, $child_element)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     * @covers \Cougar\Util\Xml::arrayIsAssociative
     */
    public function testToXmlWithNonAssociativeArrayAndNamedElementsWithObject() {
        $object = new \stdClass();
        $object->property = "M";
        $object->boolean = true;
        $array = array("a", "b", "c", "d", "e", true, $object);
        $root_element = "alphabet";
        $child_element = "letter";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        foreach($array as $value)
        {
            if (is_bool($value))
            {
                $expected_xml->addChild($child_element, "true");
            }
            else if (! is_object($value))
            {
                $expected_xml->addChild($child_element, $value);
            }
        }
        $object_xml = $expected_xml->addChild($child_element);
        $object_xml->addChild("property", $object->property);
        $object_xml->addChild("boolean", "true");
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($array, $root_element, $child_element)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     * @covers \Cougar\Util\Xml::arrayIsAssociative
     */
    public function testToXmlWithNonAssociativeJaggedArray() {
        $array = array("a", "b", "c", "d", "e",
            array("x", "y", "z", false));
        $root_element = "response";
        $child_element = "object";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        foreach($array as $value)
        {
            if (! is_array($value))
            {
                $expected_xml->addChild($child_element, $value);
            }
        }
        $child = $expected_xml->addChild($child_element);
        foreach($array[5] as $value)
        {
            if (is_bool($value))
            {
                $child->addChild($child_element, "false");
            }
            else
            {
                $child->addChild($child_element, $value);
            }
        }
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($array)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     * @covers \Cougar\Util\Xml::arrayIsAssociative
     */
    public function testToXmlWithAssociativeArray() {
        $array = array("a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5,
            "boolean" => true);
        $root_element = "associative_array";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        foreach($array as $element => $value)
        {
            if ($element == "boolean")
            {
                $expected_xml->addChild($element, "true");
            }
            else
            {
                $expected_xml->addChild($element, $value);
            }
        }
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($array, $root_element)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     * @covers \Cougar\Util\Xml::arrayIsAssociative
     */
    public function testToXmlWithAssociativeArrayAsList() {
        $array = array("a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5,
            "boolean" => true);
        $root_element = "associative_array";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        foreach($array as $element => $value)
        {
            if ($element == "boolean")
            {
                $child = $expected_xml->addChild("child", "true");
                $child->addAttribute("id", $element);
            }
            else
            {
                $child = $expected_xml->addChild("child", $value);
                $child->addAttribute("id", $element);
            }
        }
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($array, $root_element, "child", true)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     * @covers \Cougar\Util\Xml::arrayIsAssociative
     */
    public function testToXmlWithAssociativeArrayNumericIndexes() {
        $array = array(
            "2012" => array("begin" => "2012-01-01", "end" => "2012-12-31"),
            "2013" => array("begin" => "2013-01-01", "end" => "2013-12-31"),
            "2014" => array("begin" => "2013-01-01", "end" => "2014-12-31"),
            "-1" => array("begin" => true, "end" => false)
        );
        $root_element = "associative_array_numeric_index";
        $child_element = "year";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        foreach($array as $element => $value)
        {
            $child = $expected_xml->addChild($child_element);
            $child->addAttribute("id", $element);
            if ($element == "-1")
            {
                $child->addChild("begin", "true");
                $child->addChild("end", "false");
            }
            else
            {
                $child->addChild("begin", $value["begin"]);
                $child->addChild("end", $value["end"]);
            }
        }
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($array, $root_element, $child_element)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     * @covers \Cougar\Util\Xml::arrayIsAssociative
     */
    public function testToXmlWithAssociativeJaggedArray() {
        $array = array("a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5,
            "extra" => array("x" => "foo", "y" => "bar", "z" => "baz"));
        $root_element = "jagged_array";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        foreach($array as $element => $value)
        {
            if (! is_array($value))
            {
                $expected_xml->addChild($element, $value);
            }
        }
        $child = $expected_xml->addChild("extra");
        foreach($array["extra"] as $element => $value)
        {
            $child->addChild($element, $value);
        }
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($array, $root_element)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     * @covers \Cougar\Util\Xml::arrayIsAssociative
     */
    public function testToXmlWithAssociativeArrayWithObject() {
        $object = new \stdClass();
        $object->property = "value";
        $array = array("a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5,
            "object" => $object);
        $root_element = "associative_array";
        
        $expected_xml =
            new SimpleXMLElement("<" . $root_element . "/>");
        foreach($array as $element => $value)
        {
            if (!is_object($value))
            {
                $expected_xml->addChild($element, $value);
            }
            else
            {
                $child = $expected_xml->addChild($element);
                foreach($value as $child_element => $child_value)
                {
                    $child->addChild($child_element, $child_value);
                }
            }
        }
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($array, $root_element)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     */
    public function testToXmlWithSimpleObject() {
        $object = new \StdClass();
        $object->property1 = "value1";
        $object->property2 = "value2";
        $object->property3 = "value3";
        $object->property4 = "value4";
        
        $expected_xml =
            new SimpleXMLElement("<stdClass/>");
        foreach($object as $element => $value)
        {
            $expected_xml->addChild($element, $value);
        }
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($object)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toXml
     * @covers \Cougar\Util\Xml::iteratableToXml
     * @covers \Cougar\Util\Xml::arrayIsAssociative
     */
    public function testToXmlWithComplexObject() {
        $object = new \stdClass();
        $object->property1 = "value1";
        $object->property2 = "value2";
        $object->property3 = "value3";
        $object->property4 = "value4";
        $object->subClass = new \stdClass();
        $object->subClass->subProperty1 = "subvalue1";
        $object->subClass->subProperty2 = "subvalue2";
        $object->array = array("a", "b", "c", "d", "e");
        $object->jaggedArray = array("a", "b", "c", "d", "e",
            array("x", "y", "z"));
        $object->associativeArray =
            $array = array("a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5);
        $object->jaggedAssociativeArray =
            array("a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5,
                "extra" => array("x" => "foo", "y" => "bar", "z" => "baz"));

        
        $expected_xml =
            new SimpleXMLElement("<stdClass/>");
        foreach($object as $element => $value)
        {
            if (! is_array($value) & ! is_object($value))
            {
                $expected_xml->addChild($element, $value);
            }
        }
        $sub_class = $expected_xml->addChild("subClass");
        foreach($object->subClass as $element => $value)
        {
            $sub_class->addChild($element, $value);
        }
        $array = $expected_xml->addChild("array");
        foreach($object->array as $value)
        {
            $array->addChild("array", $value);
        }
        $jagged_array = $expected_xml->addChild("jaggedArray");
        foreach($object->jaggedArray as $value)
        {
            if (! is_array($value))
            {
                $jagged_array->addChild("jaggedArray", $value);
            }
        }
        $jagged_array = $jagged_array->addChild("jaggedArray");
        foreach($object->jaggedArray[5] as $value)
        {
            $jagged_array->addChild("jaggedArray", $value);
        }
        $associative_array = $expected_xml->addChild("associativeArray");
        foreach($object->associativeArray as $element => $value)
        {
            $associative_array->addChild($element, $value);
        }
        $jagged_associative_array =
            $expected_xml->addChild("jaggedAssociativeArray");
        foreach($object->jaggedAssociativeArray as $element => $value)
        {
            if (! is_array($value))
            {
                $jagged_associative_array->addChild($element, $value);
            }
        }
        $child = $jagged_associative_array->addChild("extra");
        foreach($object->jaggedAssociativeArray["extra"] as $element => $value)
        {
            $child->addChild($element, $value);
        }
        
        $this->assertEquals($expected_xml->asXML(),
            Xml::toXml($object)->asXML());
    }

    /**
     * @covers \Cougar\Util\Xml::toArray
     * @todo   Implement testToArray().
     */
    public function testToArray() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers \Cougar\Util\Xml::toObject
     * @todo   Implement testToObject().
     */
    public function testToObject() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

}
