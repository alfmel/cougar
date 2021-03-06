<?php

namespace Cougar\RestService;

/**
 * Extends the RestService interface and adds the ability to create secure, web
 * service bindings based on annotations on a method's documentation block.
 * When services are bound and you call the handleRequest() method, the object
 * will find the appropriate method call to use, authenticate the caller (if
 * required) call the method, and generate a response from the method's return
 * value.
 *
 * Annotations can be specified either in a class, or in the interface
 * implemented by the class. If you adhere to Interface Driven Development (IDD)
 * principles, the annotations should exist at the interface level since this is
 * where the API contract is established.
 * 
 * The annotation name is case sensitive, with all other aspects being
 * case-insensitive. Paths are case sensitive. The supported annotations are:
 * 
 * @Path /uri/path[/:named_param[:type[:optional_regex_match]]][/:param_array+]
 *   The Path annotation provides one or more matching URI patterns for the
 *   service. By prefixing a colon to a path element the element will be treated
 *   as a method parameter that will be passed to the method at call time. The
 *   name of the parameter must match the method's parameter name. Parameters
 *   can have an optional scalar type. At run time the parameter will be cast to
 *   that given type. Parameters can also include a regular expression to limit
 *   the values that are accepted by the method.
 *
 *   If you have a varying number of parameters, you may specify a + at the end
 *   of the parameter name to collect all remaining parameters into an array.
 *
 *   The path literals (those areas that are not tied to parameters) may also
 *   contain regular expressions (for example, /path/to/[0-9]/resource).
 * 
 * @Methods GET|POST|PUT|DELETE
 *   A list of methods the function will handle. Multiple values are separated
 *   by white space. If this annotation is omitted, the method will be called
 *   on any HTTP method.
 * 
 * @Accepts JSON|XML|PHP|mime/type
 *   A list of mime types the call will accept. You may use the generic JSON,
 *   XML and PHP keywords for their respective types. If this annotation is
 *   omitted, the method will be called with any incoming data type.
 * 
 * @Returns JSON|XML|PHP|mime/type
 *   The type of data the method will return. This makes it possible to separate
 *   calls to handle different data output types. If this annotation is omitted,
 *   the method will be called on any requested data type.
 *
 * @Resource class [alternate_name]
 *   Usually the resource type is determined from the return value of the method
 *   or the input parameters. However, when this is not possible or you wish to
 *   override the auto-detection method, you may use this annotation. The first
 *   argument is the fully-qualified class name that describes the resource. If
 *   you wish to provide a different name, you may do so using the second
 *   argument. This annotation is particularly useful in methods that don't
 *   return a value, such as delete operations.
 *
 * @Action action_name
 *   Specify the action performed on the resource. This should be a single word
 *   that describes the action (like get, read, list, query, update, delete,
 *   etc.) If the action is not specified it will be derived from the Methods
 *   annotation and the data it returns.
 * 
 * @XmlRootElement name
 *   This value will set the name of the root element when converting a response
 *   to XML without an XSD. This value will only be used if the method returns
 *   an array of other objects. If the root element name is not specified,
 *   the root element will default to "response."
 * 
 * @XmlObjectName name
 *   This value will set the name of the child XML elements when converting a
 *   response to XML without an XSD. This value will only be used when the
 *   method returns a non-associative array. This value will be given to all
 *   array elements. If object name is not specified, the object name will
 *   default to "object."
 *
 * @XmlObjectList
 *   Force the object to be treated as a list. All first-level elements will be
 *   created as elements named XmlObjectList and their indexes will be stored as
 *   the id attribute of the tag. Useful when you have a list of objects in an
 *   associative array.
 *
 * @XSD /path/to/definition.xsd
 *   Path to the XSD which will convert the returned object into an XML object.
 *   If no XSD is specified, a generic object-to-xml transformation will be
 *   made.
 *   NOTE: Has not been implemented yet (no XSD library yet)
 * 
 * @XSL /path/to/definition.xsl
 *   Path to the XSL transformation to convert the XML object into HTML.
 * 
 * @UriArray method_param_name
 *   Binds the $_URI array to the specified method parameter.
 * 
 * @GetArray method_param_name
 *   Binds the $_GET array to the specified method parameter.
 * 
 * @GetValue string|int|float|bool|set get_variable_name [method_param_name]
 *   Binds a GET variable with the method's variable. If the GET variable name
 *   is the same as the method's name, you only need to specify it once.
 * 
 * @GetQuery method_param_name
 *   Parses the URI's GET query into a list of QueryParameter objects and passes
 *   them as the given method parameter.
 * 
 * @PostArray method_param_name
 *   Binds the $_POST array to the specified method parameter.
 * 
 * @PostValue string|int|float|bool|set post_variable_name [method_param_name]
 *   Binds a POST variable with the method's variable. If the POST variable name
 *   is the same as the method's name, you only need to specify it once.
 * 
 * @Body [method_param_name] [XML|OBJECT|ARRAY|PHP]
 *   Binds the body with the method's variable. If the name of the variable
 *   is omitted, the $body variable will used. Optionally, you may specify that
 *   the data must be parsed and that the resulting object should be passed
 *   instead. If the data comes in XML, using XML will return data in a
 *   SimpleXmlObject. If the data comes in as JSON and OBJECT is specified, it
 *   will be parsed and return as an instance of stdClass (or assoc. array if
 *   ARRAY is used. Finally if the incoming data is a serialized PHP object, use
 *   PHP to unserialize it.
 * 
 * @Authentication required|optional
 *   Tells the security object to authenticate. If authentication is required
 *   and all authentication mechanisms fail, the call will return a 401 error.
 * 
 * To enforce security, this class requires a properly configured Security
 * object.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.04.15:
 *   (AT)  Added support for Resource and Action annotations
 * 2014.05.12:
 *   (AT)  Updates to annotations for API documentation
 *
 * @version 2014.04.15
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iAnnotatedRestService extends iRestService
{
    /**
     * Stores the Security object and initializes the REST request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param \Cougar\Security\iSecurity $security
     *   Reference to Security context object
     */
    public function __construct(\Cougar\Security\iSecurity $security);

    /**
     * Binds all the services in the given object. This call can be made as
     * many time as necessary to bind as many objects as desired.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param object $object Reference to the object that will be bound
     */
    public function bindFromObject(&$object);
    
    /**
     * Handles the incoming request with one of the bound objects. This is a
     * terminal call, meaning that the proper method will be called and will
     * automatically send the data to the browser. If an error occurs, it will
     * be caught and sent to the browser.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function handleRequest();
}
?>
