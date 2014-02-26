<?php

namespace Cougar\UnitTests\Util;

use Cougar\RestService\AnnotatedRestService;
use Cougar\Util\Annotation;
use Cougar\Util\Annotations;
use Cougar\Util\ClassAnnotations;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-07-09 at 11:12:07.
 */
class AnnotationsTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * @covers Cougar\Util\Annotations::extractFromDocumentBlock
     */
    public function testExtractFromDocumentBlock()
    {
        $block = '/**
                   * Something at the start
                   *
                   * @EmptyAnnotation
                   * @Annotation with some value
                   *
                   * @author john.doe@example.com
                   * @var string $abc
                   *    Some long description
                   * @var string $cde
                   * @return array Things and stuff
                   */';

        $annotations = Annotations::extractFromDocumentBlock($block);

        $this->assertCount(6, $annotations);
        $this->assertEquals("_comment", $annotations[0]->name);
        $this->assertEquals("Something at the start", $annotations[0]->value);

        $this->assertEquals("EmptyAnnotation", $annotations[1]->name);
        $this->assertEquals("", $annotations[1]->value);

        $this->assertEquals("Annotation", $annotations[2]->name);
        $this->assertEquals("with some value", $annotations[2]->value);

        $this->assertEquals("var", $annotations[3]->name);
        $this->assertEquals("string \$abc\nSome long description",
            $annotations[3]->value);

        $this->assertEquals("var", $annotations[4]->name);
        $this->assertEquals("string \$cde", $annotations[4]->value);

        $this->assertEquals("return", $annotations[5]->name);
        $this->assertEquals("array Things and stuff", $annotations[5]->value);
    }

    /**
     * @covers Cougar\Util\Annotations::extractFromDocumentBlock
     */
    public function testExtractFromDocumentBlockNoComment()
    {
        $block = '/**
                   * @covers SomeClass::SomeMethod
                   */';

        $annotations = Annotations::extractFromDocumentBlock($block);

        $this->assertCount(1, $annotations);
        $this->assertEquals("covers", $annotations[0]->name);
        $this->assertEquals("SomeClass::SomeMethod", $annotations[0]->value);
    }

    /**
     * @covers Cougar\Util\Annotations::merge
     */
    public function testMerge()
    {
        $annotations1 = new ClassAnnotations();
        $annotations1->class[] = new Annotation("C1", "Class 1");
        $annotations1->properties["P1"][] =
            new Annotation("C1.P1", "Property 1 from Class 1");
        $annotations1->properties["P2"][] =
            new Annotation("C1.P2", "Property 2 from Class 1");
        $annotations1->methods["M1"][] =
            new Annotation("C1.M1", "Method 1 from Class 1");
        $annotations1->methods["M2"][] =
            new Annotation("C1.M2", "Method 2 from Class 1");

        $annotations2 = new ClassAnnotations();
        $annotations2->class[] = new Annotation("C2", "Class 2");
        $annotations2->properties["P2"][] =
            new Annotation("C2.P2", "Property 2 from Class 2");
        $annotations2->properties["P3"][] =
            new Annotation("C2.P3", "Property 3 from Class 2");
        $annotations1->methods["M2"][] =
            new Annotation("C2.M2", "Method 2 from Class 2");
        $annotations1->methods["M3"][] =
            new Annotation("C2.M3", "Method 3 from Class 2");

        Annotations::merge($annotations1, $annotations2);

        $merged = $annotations1;

        $this->assertCount(2, $merged->class);
        $this->assertEquals("C1", $merged->class[0]->name);
        $this->assertEquals("Class 1", $merged->class[0]->value);
        $this->assertEquals("C2", $merged->class[1]->name);
        $this->assertEquals("Class 2", $merged->class[1]->value);
        
        $this->assertCount(3, $merged->properties);
        $this->assertArrayHasKey("P1", $merged->properties);
        $this->assertCount(1, $merged->properties["P1"]);
        $this->assertEquals("C1.P1", $merged->properties["P1"][0]->name);
        $this->assertEquals("Property 1 from Class 1",
            $merged->properties["P1"][0]->value);
        $this->assertArrayHasKey("P2", $merged->properties);
        $this->assertCount(2, $merged->properties["P2"]);
        $this->assertEquals("C1.P2", $merged->properties["P2"][0]->name);
        $this->assertEquals("Property 2 from Class 1",
            $merged->properties["P2"][0]->value);
        $this->assertEquals("C2.P2", $merged->properties["P2"][1]->name);
        $this->assertEquals("Property 2 from Class 2",
            $merged->properties["P2"][1]->value);
        $this->assertArrayHasKey("P3", $merged->properties);
        $this->assertCount(1, $merged->properties["P3"]);
        $this->assertEquals("C2.P3", $merged->properties["P3"][0]->name);
        $this->assertEquals("Property 3 from Class 2",
            $merged->properties["P3"][0]->value);

        $this->assertCount(3, $merged->methods);
        $this->assertArrayHasKey("M1", $merged->methods);
        $this->assertCount(1, $merged->methods["M1"]);
        $this->assertEquals("C1.M1", $merged->methods["M1"][0]->name);
        $this->assertEquals("Method 1 from Class 1",
            $merged->methods["M1"][0]->value);
        $this->assertArrayHasKey("M2", $merged->methods);
        $this->assertCount(2, $merged->methods["M2"]);
        $this->assertEquals("C1.M2", $merged->methods["M2"][0]->name);
        $this->assertEquals("Method 2 from Class 1",
            $merged->methods["M2"][0]->value);
        $this->assertEquals("C2.M2", $merged->methods["M2"][1]->name);
        $this->assertEquals("Method 2 from Class 2",
            $merged->methods["M2"][1]->value);
        $this->assertArrayHasKey("M3", $merged->methods);
        $this->assertCount(1, $merged->methods["M3"]);
        $this->assertEquals("C2.M3", $merged->methods["M3"][0]->name);
        $this->assertEquals("Method 3 from Class 2",
            $merged->methods["M3"][0]->value);
    }

    /**
     * @covers Cougar\Util\Annotations::extractFromObject
     */
    public function testExtractFromObject() {
        # Mock the cache
        $local_cache = $this->getMock("\\Cougar\\Cache\\Cache");
        $local_cache->expects($this->any())
            ->method("get")
            ->will($this->returnValue(false));
        $local_cache->expects($this->any())
            ->method("set")
            ->will($this->returnValue(false));

        Annotations::$cache = $local_cache;
        
        # Create the object and extract annotations
        $object = new BasicAnnotationTest();
        $annotations = Annotations::extractFromObject($object);
        
        $this->assertInstanceOf("Cougar\\Util\\ClassAnnotations", $annotations);
        
        $this->assertFalse($annotations->cached);

        $this->assertCount(7, $annotations->class);
        foreach($annotations->class as $class_annotation)
        {
            $this->assertInstanceOf("Cougar\\Util\\Annotation", 
                $class_annotation);
        }

        $this->assertEquals(new Annotation("_comment",
                "BasicAnnotationTest class description."),
            $annotations->class[0]);
        $this->assertEquals(new Annotation("Annotation1", "Value 1"),
            $annotations->class[1]);
        $this->assertEquals(new Annotation("Annotation2", "Value 2"),
            $annotations->class[2]);
        $this->assertEquals(new Annotation("Annotation3",
                "Value 3\nMultiple lines"),
            $annotations->class[3]);
        $this->assertEquals(new Annotation("RepeatedAnnotation",
                "Repeated value 1"),
            $annotations->class[4]);
        $this->assertEquals(new Annotation("RepeatedAnnotation",
                "Repeated value 2"),
            $annotations->class[5]);
        $this->assertEquals(new Annotation("RepeatedAnnotation",
                "Repeated value 3"),
            $annotations->class[6]);

        $this->assertCount(2, $annotations->properties);
        $this->assertArrayHasKey("propertyA", $annotations->properties);
        $this->assertArrayHasKey("propertyB", $annotations->properties);
        $this->assertCount(2, $annotations->properties["propertyA"]);
        foreach($annotations->properties["propertyA"] as $property_annotation)
        {
            $this->assertInstanceOf("Cougar\\Util\\Annotation", 
                $property_annotation);
        }
        $this->assertArrayHasKey("propertyA", $annotations->properties);
        $this->assertCount(2, $annotations->properties["propertyA"]);
        $this->assertEquals(
            new Annotation("PropertyA", "Annotation for Property A"),
            $annotations->properties["propertyA"][0]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyA"][1]);

        foreach($annotations->properties["propertyB"] as $property_annotation)
        {
            $this->assertInstanceOf("Cougar\\Util\\Annotation", 
                $property_annotation);
        }
        $this->assertEquals(new Annotation("_comment",
                "Some other and longer property description"),
            $annotations->properties["propertyB"][0]);
        $this->assertEquals(
            new Annotation("PropertyB", "Annotation for Property B"),
            $annotations->properties["propertyB"][1]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyB"][2]);

        $this->assertCount(2, $annotations->methods);
        $this->assertArrayHasKey("methodY", $annotations->methods);
        $this->assertArrayHasKey("methodZ", $annotations->methods);

        $this->assertCount(2, $annotations->methods["methodY"]);
        $this->assertInstanceOf("Cougar\\Util\\Annotation",
            $annotations->methods["methodY"][0]);
        $this->assertInstanceOf("Cougar\\Util\\Annotation",
            $annotations->methods["methodY"][1]);
        $this->assertArrayHasKey("methodY", $annotations->methods);
        $this->assertEquals(
            new Annotation("MethodY", "Annotation for Method Y"),
            $annotations->methods["methodY"][0]);
        $this->assertEquals(new Annotation("return", "object Something"),
            $annotations->methods["methodY"][1]);

        $this->assertCount(3, $annotations->methods["methodZ"]);
        $this->assertInstanceOf("Cougar\\Util\\Annotation",
            $annotations->methods["methodZ"][0]);
        $this->assertInstanceOf("Cougar\\Util\\Annotation",
            $annotations->methods["methodZ"][1]);
        $this->assertInstanceOf("Cougar\\Util\\Annotation",
            $annotations->methods["methodZ"][2]);
        $this->assertEquals(
            new Annotation("_comment", "MethodZ rocks!"),
            $annotations->methods["methodZ"][0]);
        $this->assertEquals(
            new Annotation("MethodZ", "Annotation for Method Z"),
            $annotations->methods["methodZ"][1]);
        $this->assertEquals(new Annotation("param", "string \$zulu\n" .
                "Some meaningless variable with an even more meaningless " .
                "comment that\n" .
                "extends several lines"),
            $annotations->methods["methodZ"][2]);

        return $annotations;
    }

    /**
     * @covers Cougar\Util\Annotations::extractFromObject
     * @depends testExtractFromObject
     */
    public function testExtractFromClassNameCached(
        ClassAnnotations $non_cached_annotations)
    {
        # Extract the annotations from the class name
        $annotations = Annotations::extractFromObject(
            __NAMESPACE__ . "\\BasicAnnotationTest");
        
        $this->assertInstanceOf("Cougar\\Util\\ClassAnnotations", $annotations);
        $this->assertTrue($annotations->cached);

        # Flip the cached bit on the non-cached annotations; makes the assert
        # easier
        $non_cached_annotations->cached = true;
        $this->assertEquals($non_cached_annotations, $annotations);
    }

    /**
     * @covers Cougar\Util\Annotations::extractFromObject
     */
    public function testExtractFromClassInheritedClass()
    {
        # Extract the annotations from the class name
        $annotations = Annotations::extractFromObject(
            __NAMESPACE__ . "\\ExtendedBasicAnnotationTest");

        $this->assertCount(1, $annotations->class);
        $this->assertEquals(new Annotation("ChildAnnotation", "Child 1"),
            $annotations->class[0]);

        $this->assertCount(3, $annotations->properties);

        $this->assertArrayHasKey("propertyA", $annotations->properties);
        $this->assertCount(2, $annotations->properties["propertyA"]);
        $this->assertEquals(
            new Annotation("PropertyA", "Annotation for Property A"),
            $annotations->properties["propertyA"][0]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyA"][1]);

        $this->assertArrayHasKey("propertyB", $annotations->properties);
        $this->assertCount(2, $annotations->properties["propertyB"]);
        $this->assertEquals(
            new Annotation("PropertyB", "Annotation for Property B override"),
            $annotations->properties["propertyB"][0]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyB"][1]);

        $this->assertArrayHasKey("propertyC", $annotations->properties);
        $this->assertCount(2, $annotations->properties["propertyC"]);
        $this->assertEquals(
            new Annotation("PropertyC", "Annotation for Property C"),
            $annotations->properties["propertyC"][0]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyC"][1]);

        $this->assertCount(3, $annotations->methods);

        $this->assertArrayHasKey("methodX", $annotations->methods);
        $this->assertCount(1, $annotations->methods["methodX"]);
        $this->assertEquals(
            new Annotation("MethodX", "Annotation for Method X"),
            $annotations->methods["methodX"][0]);

        $this->assertArrayHasKey("methodY", $annotations->methods);
        $this->assertCount(1, $annotations->methods["methodY"]);
        $this->assertEquals(
            new Annotation("MethodY", "Annotation for Method Y override"),
            $annotations->methods["methodY"][0]);

        $this->assertArrayHasKey("methodZ", $annotations->methods);
        $this->assertCount(3, $annotations->methods["methodZ"]);
        $this->assertEquals(
            new Annotation("_comment", "MethodZ rocks!"),
            $annotations->methods["methodZ"][0]);
        $this->assertEquals(
            new Annotation("MethodZ", "Annotation for Method Z"),
            $annotations->methods["methodZ"][1]);
        $this->assertEquals(new Annotation("param", "string \$zulu\n" .
                "Some meaningless variable with an even more meaningless " .
                    "comment that\n" .
                "extends several lines"),
            $annotations->methods["methodZ"][2]);
    }


    /**
     * @covers Cougar\Util\Annotations::extractFromObject
     */
    public function testExtractFromClassInheritedClassNotAllMembers()
    {
        # Extract the annotations from the class name
        $annotations = Annotations::extractFromObject(
            __NAMESPACE__ . "\\ExtendedBasicAnnotationTest", false);

        $this->assertCount(1, $annotations->class);
        $this->assertEquals(new Annotation("ChildAnnotation", "Child 1"),
            $annotations->class[0]);

        $this->assertCount(2, $annotations->properties);

        $this->assertArrayHasKey("propertyB", $annotations->properties);
        $this->assertCount(2, $annotations->properties["propertyB"]);
        $this->assertEquals(
            new Annotation("PropertyB", "Annotation for Property B override"),
            $annotations->properties["propertyB"][0]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyB"][1]);

        $this->assertArrayHasKey("propertyC", $annotations->properties);
        $this->assertCount(2, $annotations->properties["propertyC"]);
        $this->assertEquals(
            new Annotation("PropertyC", "Annotation for Property C"),
            $annotations->properties["propertyC"][0]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyC"][1]);

        $this->assertCount(2, $annotations->methods);

        $this->assertArrayHasKey("methodX", $annotations->methods);
        $this->assertCount(1, $annotations->methods["methodX"]);
        $this->assertEquals(
            new Annotation("MethodX", "Annotation for Method X"),
            $annotations->methods["methodX"][0]);

        $this->assertArrayHasKey("methodY", $annotations->methods);
        $this->assertCount(1, $annotations->methods["methodY"]);
        $this->assertEquals(
            new Annotation("MethodY", "Annotation for Method Y override"),
            $annotations->methods["methodY"][0]);
    }

    /**
     * @covers Cougar\Util\Annotations::extractFromObjectWithInheritance
     */
    public function testExtractFromObjectwithInheritance() {
        # Extract the annotations from the class name
        $annotations = Annotations::extractFromObjectWithInheritance(
            __NAMESPACE__ . "\\ExtendedBasicAnnotationTest");

        $this->assertCount(8, $annotations->class);
        $this->assertEquals(new Annotation("_comment",
                "BasicAnnotationTest class description."),
            $annotations->class[0]);
        $this->assertEquals(new Annotation("Annotation1", "Value 1"),
            $annotations->class[1]);
        $this->assertEquals(new Annotation("Annotation2", "Value 2"),
            $annotations->class[2]);
        $this->assertEquals(new Annotation("Annotation3",
                "Value 3\nMultiple lines"),
            $annotations->class[3]);
        $this->assertEquals(new Annotation("RepeatedAnnotation",
                "Repeated value 1"),
            $annotations->class[4]);
        $this->assertEquals(new Annotation("RepeatedAnnotation",
                "Repeated value 2"),
            $annotations->class[5]);
        $this->assertEquals(new Annotation("RepeatedAnnotation",
                "Repeated value 3"),
            $annotations->class[6]);
        $this->assertEquals(new Annotation("ChildAnnotation", "Child 1"),
            $annotations->class[7]);

        $this->assertCount(3, $annotations->properties);

        $this->assertArrayHasKey("propertyA", $annotations->properties);
        $this->assertCount(2, $annotations->properties["propertyA"]);
        $this->assertEquals(
            new Annotation("PropertyA", "Annotation for Property A"),
            $annotations->properties["propertyA"][0]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyA"][1]);

        $this->assertArrayHasKey("propertyB", $annotations->properties);
        $this->assertCount(5, $annotations->properties["propertyB"]);
        $this->assertEquals(new Annotation("_comment",
                "Some other and longer property description"),
            $annotations->properties["propertyB"][0]);
        $this->assertEquals(
            new Annotation("PropertyB", "Annotation for Property B"),
            $annotations->properties["propertyB"][1]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyB"][2]);
        $this->assertEquals(
            new Annotation("PropertyB", "Annotation for Property B override"),
            $annotations->properties["propertyB"][3]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyB"][4]);

        $this->assertArrayHasKey("propertyC", $annotations->properties);
        $this->assertCount(2, $annotations->properties["propertyC"]);
        $this->assertEquals(
            new Annotation("PropertyC", "Annotation for Property C"),
            $annotations->properties["propertyC"][0]);
        $this->assertEquals(new Annotation("var", "string Some value"),
            $annotations->properties["propertyC"][1]);

        $this->assertCount(3, $annotations->methods);

        $this->assertArrayHasKey("methodX", $annotations->methods);
        $this->assertCount(1, $annotations->methods["methodX"]);
        $this->assertEquals(
            new Annotation("MethodX", "Annotation for Method X"),
            $annotations->methods["methodX"][0]);

        $this->assertArrayHasKey("methodY", $annotations->methods);
        $this->assertCount(3, $annotations->methods["methodY"]);
        $this->assertEquals(
            new Annotation("MethodY", "Annotation for Method Y"),
            $annotations->methods["methodY"][0]);
        $this->assertEquals(new Annotation("return", "object Something"),
            $annotations->methods["methodY"][1]);
        $this->assertEquals(
            new Annotation("MethodY", "Annotation for Method Y override"),
            $annotations->methods["methodY"][2]);

        $this->assertArrayHasKey("methodZ", $annotations->methods);
        $this->assertCount(3, $annotations->methods["methodZ"]);
        $this->assertEquals(
            new Annotation("_comment", "MethodZ rocks!"),
            $annotations->methods["methodZ"][0]);
        $this->assertEquals(
            new Annotation("MethodZ", "Annotation for Method Z"),
            $annotations->methods["methodZ"][1]);
        $this->assertEquals(new Annotation("param", "string \$zulu\n" .
                "Some meaningless variable with an even more meaningless " .
                "comment that\n" .
                "extends several lines"),
            $annotations->methods["methodZ"][2]);
    }

    /**
     * @covers Cougar\\Util\\Annotations::extract
     */
    public function testFilteredAnnotations() {
        # Mock the cache
        $local_cache = $this->getMock("\\Cougar\\Cache\\Cache");
        $local_cache->expects($this->any())
            ->method("get")
            ->will($this->returnValue(false));
        $local_cache->expects($this->any())
            ->method("set")
            ->will($this->returnValue(false));
        
        # Create the object and extract annotations
        $object = new BasicFilteredAnnotationTest();
        $annotations = Annotations::extract($local_cache, $object);
        
        $this->assertInstanceOf("Cougar\\Util\\ClassAnnotations", $annotations);
        
        $this->assertFalse($annotations->cached);
        
        $this->assertCount(1, $annotations->class);
        $this->assertInstanceOf("Cougar\\Util\\Annotation",
            $annotations->class[0]);
        $this->assertEquals("Annotation", $annotations->class[0]->name);
        $this->assertEquals("First annotation",
            $annotations->class[0]->value);
        
        $this->assertCount(1, $annotations->properties);
        $this->assertArrayHasKey("stuff", $annotations->properties);
        $this->assertCount(1, $annotations->properties["stuff"]);
        $this->assertInstanceOf("Cougar\\Util\\Annotation",
            $annotations->properties["stuff"][0]);
        $this->assertEquals("var",
            $annotations->properties["stuff"][0]->name);
        $this->assertEquals("string Stuff",
            $annotations->properties["stuff"][0]->value);
        
        $this->assertCount(1, $annotations->methods);
        $this->assertArrayHasKey("doStuff", $annotations->methods);
        $this->assertCount(3, $annotations->methods["doStuff"]);
    }
    
    /**
     * @covers Cougar\\Util\\Annotations::extract
     * @covers Cougar\\Util\\Annotations::extractFromObject
     * @covers Cougar\\Util\\Annotations::extractFromObjectWithInheritance
     */
    public function testTraitAnnotationsFromObjectWithInheritance() {
        # Mock the cache
        $local_cache = $this->getMock("\\Cougar\\Cache\\Cache");
        $local_cache->expects($this->any())
            ->method("get")
            ->will($this->returnValue(false));
        $local_cache->expects($this->any())
            ->method("set")
            ->will($this->returnValue(false));

        # Create the object and extract annotations
        $object = new BasicTraitAnnotationTest();
        $annotations = Annotations::extract($local_cache, $object);
        
        $this->assertInstanceOf("Cougar\\Util\\ClassAnnotations", $annotations);

        $this->assertCount(3, $annotations->class);
        $this->assertEquals("Trait", $annotations->class[0]->name);
        $this->assertEquals("This is a trait annotation",
            $annotations->class[0]->value);
        $this->assertEquals("Class", $annotations->class[1]->name);
        $this->assertEquals("This defines class in the trait",
            $annotations->class[1]->value);
        $this->assertEquals("Class", $annotations->class[2]->name);
        $this->assertEquals("This is the class annotation",
            $annotations->class[2]->value);

        $this->assertCount(1, $annotations->properties);
        $this->assertArrayHasKey("traitStuff", $annotations->properties);
        $this->assertCount(1, $annotations->properties["traitStuff"]);
        $this->assertInstanceOf("Cougar\\Util\\Annotation",
            $annotations->properties["traitStuff"][0]);
        $this->assertEquals("var",
            $annotations->properties["traitStuff"][0]->name);
        $this->assertEquals("string traitVar",
            $annotations->properties["traitStuff"][0]->value);
        
        $this->assertCount(1, $annotations->methods);
        $this->assertArrayHasKey("doClassStuff", $annotations->methods);
        $this->assertCount(1, $annotations->methods["doClassStuff"]);
        $this->assertEquals("MethodAnnotation",
            $annotations->methods["doClassStuff"][0]->name);
        $this->assertEquals("Hello World!",
            $annotations->methods["doClassStuff"][0]->value);
    }

    /**
     * @covers Cougar\\Util\\Annotations::extractFromObject
     */
    public function testInterfaceAnnotationsFromObjectWithInheritance()
    {
        $annotations = Annotations::extractFromObjectWithInheritance(
            __NAMESPACE__ . "\\BasicClassFromInterface");

        $this->assertInstanceOf("Cougar\\Util\\ClassAnnotations", $annotations);

        $this->assertCount(2, $annotations->class);
        $this->assertEquals(new Annotation("Interface", "Interface annotation"),
            $annotations->class[0]);
        $this->assertCount(2, $annotations->class);
        $this->assertEquals(new Annotation("Class", "Class annotation"),
            $annotations->class[1]);

        $this->assertCount(0, $annotations->properties);

        $this->assertCount(1, $annotations->methods);
        $this->assertArrayHasKey("doSomething", $annotations->methods);
        $this->assertCount(4, $annotations->methods["doSomething"]);
        $this->assertEquals(new Annotation("_comment",
                "This is a method in an interface"),
            $annotations->methods["doSomething"][0]);
        $this->assertEquals(new Annotation("param", "int \$number"),
            $annotations->methods["doSomething"][1]);
        $this->assertEquals(new Annotation("_comment",
                "This is the description in the implementation"),
            $annotations->methods["doSomething"][2]);
        $this->assertEquals(new Annotation("param", "int \$number Some number"),
            $annotations->methods["doSomething"][3]);
    }
}

/**
 * BasicAnnotationTest class description.
 *
 * @Annotation1 Value 1
 * @Annotation2 Value 2
 * @Annotation3 Value 3
 *   Multiple lines
 * @RepeatedAnnotation Repeated value 1
 * @RepeatedAnnotation Repeated value 2
 * @RepeatedAnnotation Repeated value 3
 */
class BasicAnnotationTest
{
    /**
     * @PropertyA Annotation for Property A
     * @var string Some value
     */
    public $propertyA;
    
    /**
     * Some other and longer property description
     *
     * @PropertyB Annotation for Property B
     * @var string Some value
     */
    
    public $propertyB;
    
    /**
     * @MethodY Annotation for Method Y
     * @return object Something
     */
    public function methodY() { }
    
    /**
     * MethodZ rocks!
     *
     * @MethodZ Annotation for Method Z
     * @param string $zulu
     *   Some meaningless variable with an even more meaningless comment that
     *   extends several lines
     */
    public function methodZ($zulu) { }
}

/**
 * @ChildAnnotation Child 1
 */
class ExtendedBasicAnnotationTest extends BasicAnnotationTest
{
    /**
     * @PropertyB Annotation for Property B override
     * @var string Some value
     */
    public $propertyB;
    
    /**
     * @PropertyC Annotation for Property C
     * @var string Some value
     */
    public $propertyC;
    
    /**
     * @MethodX Annotation for Method X
     */
    public function methodX() { }
    
    /**
     * @MethodY Annotation for Method Y override
     */
    public function methodY() { }
}

/**
 * @Annotation First annotation
 * @author should be ignored
 */
class BasicFilteredAnnotationTest
{
    /**
     * @todo Should not show up
     * @var string Stuff
     */
    public $stuff;
    
    /**
     * @author Some dude
     * @todo Make it better!
     * @version 1.2.3
     * @param string stuff
     * @return mixed More stuff
     */
    public function doStuff($stuff) { }
}

/**
 * @Trait This is a trait annotation
 * @Class This defines class in the trait
 */
trait BasicTrait
{
    /**
     * @var string traitVar
     */
    public $traitStuff;
}

/**
 * @Class This is the class annotation
 */
class BasicTraitAnnotationTest
{
    use BasicTrait;
    
    /**
     * @MethodAnnotation Hello World!
     */
    public function doClassStuff()
    {
        
    }
}

/**
 * @Interface Interface annotation
 */
interface BasicInterface
{
    /**
     * This is a method in an interface
     *
     * @param int $number
     */
    public function doSomething($number);
}

/**
 * @Class Class annotation
 */
class BasicClassFromInterface implements BasicInterface
{
    /**
     * This is the description in the implementation
     *
     * @param int $number Some number
     */
    public function doSomething($number) { }
}
?>
