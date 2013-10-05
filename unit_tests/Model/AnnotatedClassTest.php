<?php

namespace Cougar\UnitTests\Model;

use Cougar\Model\iAnnotatedClass;
use Cougar\Model\tAnnotatedClass;
use Cougar\Model\AnnotatedClass;

require_once(__DIR__ . "/../../../cougar.php");

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-08-06 at 06:56:15.
 */
class AnnotatedClassTest extends \PHPUnit_Framework_TestCase {

    public function testAnnotationExtraction() {
        $object = new AnnotatedClassUnitTest();
        $annotations = $object->getAnnotations();
        $this->assertInstanceOf("Cougar\Util\ClassAnnotations", $annotations);
        $this->assertCount(1, $annotations->class);
        $this->assertCount(1, $annotations->properties);
        $this->assertCount(1, $annotations->methods);
    }

    public function testAnnotationExtractionViaTrait() {
        $object = new AnnotatedClassUnitTestViaTrait();
        $annotations = $object->getAnnotations();
        $this->assertInstanceOf("Cougar\Util\ClassAnnotations", $annotations);
        $this->assertCount(1, $annotations->class);
        $this->assertCount(1, $annotations->properties);
        $this->assertCount(1, $annotations->methods);
    }
}


/**
 * @ClassAnnotation
 */
class AnnotatedClassUnitTest extends AnnotatedClass
{
    /**
     * @PropertyAnnotation
     */
    public $propertyA;
    
    /**
     * @MethodAnnotation
     */
    public function getAnnotations()
    {
        return $this->__annotations;
    }
}

/**
 * @ClassAnnotation
 */
class AnnotatedClassUnitTestViaTrait implements iAnnotatedClass
{
    use tAnnotatedClass;
    
    /**
     * @PropertyAnnotation
     */
    public $propertyA;
    
    /**
     * @MethodAnnotation
     */
    public function getAnnotations()
    {
        return $this->__annotations;
    }
}
?>
