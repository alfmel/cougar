<?php

namespace Cougar\RestService;

use Cougar\RestService\Models\Application;
use Cougar\RestService\Models\Component;
use Cougar\RestService\Models\Resource;
use Cougar\RestService\Models\Action;
use Cougar\RestService\Models\Parameter;
use Cougar\RestService\Models\ResourceDescription;
use Cougar\RestService\Models\ReturnValue;
use Cougar\RestService\Models\Value;
use Cougar\Util\Annotations;
use Cougar\Util\Arrays;
use Cougar\Exceptions\Exception;
use Cougar\Exceptions\NotFoundException;

/**
 * The API Builder creates API documentation from interfaces and classes.
 *
 * @history
 * 2014.04.15:
 *   (AT)  Initial implementation
 * 2014.05.29:
 *   (AT)  Added shortDescription field to resource list
 * 2014.05.30:
 *   (AT)  Added the actions parameter to getResources() to be able to retrieve
 *         actions in one call
 * 2014.06.03:
 *   (AT)  Fixed issue where action name was not properly set when the @Action
 *         annotation was used
 *
 * @version 2014.06.03
 * @package Cougar
 * @license MIT
 *
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class ApiDocumentation implements iApiDocumentation
{
    /**
     * Sets the name of the application
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial implementation
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $url_prefix
     *   Application's URL prefix
     * @param string $application_name
     *   Application name
     * @param string $application_description
     *   Application description (optional)
     */
    public function __construct($url_prefix, $application_name,
        $application_description = "")
    {
        // Set the values
        $this->setUrlPrefix($url_prefix);
        $this->setApplicationName($application_name);

        if ($application_description)
        {
            $this->setApplicationDescription($application_description);
        }
    }


    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * Sets the human-readable name of the application.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial implementation
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $name
     *   Application name
     * @throws \Cougar\Exceptions\Exception
     */
    public function setApplicationName($name)
    {
        if ($name)
        {
            $this->applicationName = $name;
        }
        else
        {
            throw new Exception("Application must have a name");
        }
    }

    /**
     * Sets the application description.
     *
     * @history
     * 2014.05.12:
     *   (AT)  Initial definition
     *
     * @version 2014.05.12
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $description
     *   Application description
     * @throws \Cougar\Exceptions\Exception
     */
    public function setApplicationDescription($description)
    {
        $this->applicationDescription = (string) $description;
    }

    /**
     * Sets the application's URL prefix. This is the base URL that marks the
     * entry point into the application.
     *
     * @history
     * 2014.05.12:
     *   (AT)  Initial definition
     *
     * @version 2014.05.12
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $url_prefix
     *   URL prefix
     * @throws \Cougar\Exceptions\Exception
     */
    public function setUrlPrefix($url_prefix)
    {
        if (filter_var($url_prefix, FILTER_VALIDATE_URL))
        {
            $this->urlPrefix = $url_prefix;
        }
        else
        {
            throw new Exception("Invalid URL prefix");
        }
    }

    /**
     * Returns basic information about the application. The information
     * includes the application name, description and URL prefix.
     *
     * @history
     * 2014.05.12:
     *   (AT)  Initial definition
     *
     * @version 2014.05.12
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @return \Cougar\RestService\Models\Application
     */
    public function getApplicationInformation()
    {
        // Get a new Application model and set the values
        $application = new Application();
        $application->name = $this->applicationName;
        $application->description = $this->applicationDescription;
        $application->urlPrefix = $this->urlPrefix;

        // Return the values
        return $application;
    }

    /**
     * Adds a component to the application. A component is a subset of the application
     * functionality. You must provide a interface or class name or a list of
     * interfaces and class name that are part of the component.
     *
     * The Component ID should be a human-readable name for the component, one or two
     * words without spaces and URL-safe.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial implementation
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $component_id
     *   A short, URI-safe descriptive name for the component
     * @param string $component_name
     *   A human-readable name for the component
     * @param mixed $class_list
     *   String with fully-qualified class name or array of class names
     * @param string $description
     *   Description for the functionality of the component (optional); if
     *   not specified, description will be taken from class documentation
     *   block
     * @throws \Cougar\Exceptions\Exception
     */
    public function addComponent($component_id, $component_name, $class_list,
        $description = null)
    {
        // Make sure the component has not been defined already
        if (array_key_exists($component_id, $this->components))
        {
            throw new Exception("Component \"" . $component_id .
                "\" has already been defined");
        }

        // Create a new component and set the ID and name
        $component = new Component();
        $component->componentId = $component_id;
        $component->name = $component_name;

        // See if we have a description
        if ($description)
        {
            $component->description = $description;
        }

        // See if the class list is a string with a single class
        if (! is_array($class_list))
        {
            $class_list = array((string) $class_list);
        }

        // Go through the list of classes
        foreach($class_list as $class)
        {
            // Load the method_info for this class
            $class_annotations =
                Annotations::extractFromObjectWithInheritance($class);

            // See if we need to create the description from the class doc block
            if (! $description &&
                array_key_exists("_comment", $class_annotations->class))
            {
                // See if we already have a description in the component
                if ($component->description)
                {
                    $component->description .= "\n\n" .
                        $class_annotations->class["_comment"];
                }
                else
                {
                    $component->description =
                        $class_annotations->class["_comment"];
                }
            }

            // Go through each method
            foreach($class_annotations->methods as $annotations)
            {
                // Extract the method information
                $method_info = $this->parseMethodAnnotations($annotations);

                // See if this method has a Path annotation; if it doesn't have
                // one then the method is not part of the REST API
                if (! $method_info["path"])
                {
                    // Go to the next method
                    continue;
                }

                // Extract the resource
                $resource = $this->extractResource($method_info);

                // See if this resource already exists
                if (! array_key_exists($resource->name, $this->resources))
                {
                    // Add the resource to the resource lists
                    $this->resources[$resource->name] = $resource;
                    $component->resources[] = $resource;
                }
                else
                {
                    // Point the resource variable to the existing resource
                    $resource = $this->resources[$resource->name];
                }

                // Get the action on the resource
                $action =  $this->extractAction($method_info);

                // Figure out an ID for this action
                $count = 0;
                foreach($resource->actions as $tmp_action)
                {
                    if ($tmp_action->name == $action->name)
                    {
                        $count++;
                    }
                }

                if ($count)
                {
                    $action->actionId = $action->name . "." . $count;
                }
                else
                {
                    $action->actionId = $action->name;
                }

                // Add the action to the resource's action list
                $resource->actions[] = $action;
            }
        }

        // Add the component
        $this->components[] = $component;
    }

    /**
     * Returns the list of application components.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial implementation
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @return \Cougar\RestService\Models\Component[]
     *   List of available components
     */
    public function getComponents()
    {
        // Clone the list of components
        $components = Arrays::cloneObjectArray($this->components);

        // Set the components to list view
        Arrays::setModelView($components, "list");

        // Return the list of components
        return $components;
    }

    /**
     * Returns a list of resources for the given component.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial implementation
     * 2014.05.30:
     *   (AT)  Added support for the actions parameter to include actions in the
     *         return value
     *
     * @version 2014.05.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $component_id
     *   Component ID
     * @param bool $actions
     *   Whether to include the resource's actions
     * @throws \Cougar\Exceptions\NotFoundException
     * @return \Cougar\RestService\Models\Resource[]
     *   List of available resources for the given component
     */
    public function getResources($component_id = null, $actions = false)
    {
        // Go through the components
        $resources = null;
        if ($component_id)
        {
            foreach($this->components as $component)
            {
                if ($component->componentId == $component_id)
                {
                    // Get the copy of the resources for this component
                    $resources = Arrays::cloneObjectArray($component->resources);

                }
            }

            // We couldn't find this component
            if (! $resources)
            {
                throw new NotFoundException("Component does not exist");
            }
        }
        else
        {
            // Return all resources
            $resources =
                array_values(Arrays::cloneObjectArray($this->resources));
        }

        // Set the view to list
        if ($actions)
        {
            Arrays::setModelView($resources, "list_with_actions");
        }
        else
        {
            Arrays::setModelView($resources, "list");
        }

        // Return the resources
        return $resources;
    }

    /**
     * Describes the given resource. The description includes the list of
     * fields, their data types and value constraints.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial implementation
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $resource_id
     *   Resource name
     * @throws \Cougar\Exceptions\NotFoundException
     * @throws \Cougar\Exceptions\Exception
     * @return \Cougar\RestService\Models\ResourceDescription
     *   Description of the resource
     */
    public function getResource($resource_id)
    {
        // Make sure the resource exists
        if (! array_key_exists($resource_id, $this->resources))
        {
            throw new NotFoundException("Resource not found");
        }
        $resource = $this->resources[$resource_id];

        // Get the annotations
        try
        {
            $class_annotations = Annotations::extractFromObject($resource->class);
        }
        catch (Exception $e)
        {
            throw new Exception("Could not obtain information on the resource");
        }

        // Create the Resource Description object and set some values
        $resource_desc = new ResourceDescription();
        $resource_desc->resourceId = $resource->resourceId;
        $resource_desc->name = $resource->name;

        // See if we have a comment for the class
        foreach($class_annotations->class as $annotation)
        {
            if ($annotation->name == "_comment")
            {
                // Set the description
                $resource_desc->description = $annotation->value;
                break;
            }
        }

        // Go through the public properties in the object
        foreach($class_annotations->properties as $property => $annotations)
        {
            // Create a new entry for the property and give it its name
            $value = new Value();
            $value->name = $property;

            // Go through the annotations
            foreach($annotations as $annotation)
            {
                switch($annotation->name)
                {
                    case "_comment":
                        $value->description = $annotation->value;
                        break;
                    case "var":
                        // Get the values from the annotation
                        $values = array_combine(array("type", "description"),
                            array_pad(
                                preg_split('/\s+/u', $annotation->value, 2),
                                2, ""));

                        // Generate the proper name
                        $value->type = $this->generateResourceName(
                            $values["type"], $value->list);

                        // See if this is a resource
                        $filtered_resources = Arrays::dataFilter(
                            $this->resources, "name", $value->type);
                        if ($filtered_resources)
                        {
                            // Yep, it's a resource
                            $value->isResource = true;
                        }

                        // See if we have a description
                        if ($values["description"] && ! $value->description)
                        {
                            $value->description = $values["description"];
                        }
                        break;
                    case "View":
                        // See if the field is hidden or optional
                        if (mb_strpos($annotation->value, "hidden") !== false ||
                            mb_strpos($annotation->value, "optional") !== false)
                        {
                            $value->optional = true;
                        }
                        break;
                    case "Optional":
                        $value->optional = true;
                        break;
                }
            }

            // Add the value
            $resource_desc->values[] = $value;
        }

        // Return the description
        return $resource_desc;
    }

    /**
     * Returns a list of actions on the given resource.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial implementation
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $resource_id
     *   Resource name
     * @throws \Cougar\Exceptions\NotFoundException
     * @return \Cougar\RestService\Models\Action[]
     *   List of actions on resource
     */
    public function getResourceActions($resource_id)
    {
        // Make sure the resource exists
        if (! array_key_exists($resource_id, $this->resources))
        {
            throw new NotFoundException("Resource not found");
        }
        $resource = $this->resources[$resource_id];

        // Get a copy of the actions
        $actions = Arrays::cloneObjectArray($resource->actions);

        // Set the view to list
        Arrays::setModelView($actions, "list");

        // Return the actions
        return $actions;
    }

    /**
     * Returns the details for the specific action on the given resource.
     *
     * @history
     * 2014.05.07
     *   (AT)  Initial implementation
     *
     * @version 2014.05.07
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $resource_id
     *   Resource name
     * @param string $action_id
     *   Action
     * @throws \Cougar\Exceptions\NotFoundException
     * @return \Cougar\RestService\Models\Action
     *   Resource action information
     */
    public function getResourceAction($resource_id, $action_id)
    {
        // Make sure the resource exists
        if (! array_key_exists($resource_id, $this->resources))
        {
            throw new NotFoundException("Resource not found");
        }
        $resource = $this->resources[$resource_id];

        // Find the action
        foreach($resource->actions as $action)
        {
            if ($action->actionId == $action_id)
            {
                // Return this action
                return clone $action;
            }
        }

        // We couldn't find the action
        throw new NotFoundException("Action does not exist");
    }


    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * @var string Application name
     */
    protected $applicationName;

    /**
     * @var string Application description
     */
    protected $applicationDescription;

    /**
     * @var string Application's URL prefix
     */
    protected $urlPrefix;

    /**
     * @var \Cougar\RestService\Models\Component[] List of components
     */
    protected $components = array();

    /**
     * @var \Cougar\RestService\Models\Resource[] List of available resources
     */
    protected $resources = array();

    /**
     * Returns true if the given type is a primitive. If the type is an array,
     * it will check the type of the array if in the type[] notation and return
     * information about the type. So an array of strings is still considered
     * primitive but an array of stdClass won't.
     *
     * @history
     * 2014.05.09:
     *   (AT)  Initial implementation
     *
     * @version 2014.05.09
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $type
     *   Type to check
     * @return bool True if the type is primitive, false otherwise
     */
    public function isPrimitive($type)
    {
        // See if we have array notation
        if (mb_substr($type, -2) == "[]")
        {
            $type = mb_substr($type, 0, -2);
        }

        // Check the t ype
        switch($type)
        {
            case "int":
            case "integer":
            case "float":
            case "double":
            case "bool":
            case "boolean":
            case "string":
            case "array":
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Creates the name of a resource from its type (class name).
     *
     * The resource name will replace backslashes (\) with a period and make
     * sure the name does not start with a leading period.
     *
     * The method will also check for [] at the end of the name, denoting a
     * list. If those are found, the second argument (passed by reference) will
     * be set to true.
     *
     * @history
     * 2014.05.09:
     *   (AT)  Initial implementation
     *
     * @version 2014.05.09
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $type
     *   Type (fully qualified class name)
     * @param bool $list
     *   Whether the type is a list
     * @return string Resource name
     */
    protected function generateResourceName($type, &$list = false)
    {
        // See if this is a list
        if (substr($type, -2) == "[]")
        {
            // Yep, we have a list
            $list = true;

            // Remove the [] from the type name
            $type = substr($type, 0, -2);
        }

        // Convert all the \\ to .
        $name = str_replace("\\", ".", $type);

        // Make sure the name doesn't start with a .
        while ($name && $name[0] == ".")
        {
            $name = substr($name, 1);
        }

        // Return the resource name
        return $name;
    }

    /**
     * Parses a method's method_info into a more workable structure.
     *
     * @history
     * 2014.04.29:
     *   (AT)  Initial implementation
     *
     * @version 2014.04.29
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $annotations
     *   Method method_info
     * @return array Associative array of parsed method information
     */
    protected function parseMethodAnnotations(array $annotations)
    {
        // Create a structure for the method information we care about
        $method_info = array(
            "description" => "",
            "comment" => "",
            "path" => array(),
            "method" => array(),
            "resource" => null,
            "action" => "",
            "param" => array(),
            "get" => array(),
            "query" => false,
            "post" => array(),
            "body" => null,
            "authentication" => "none",
            "return" => null
        );

        foreach($annotations as $annotation)
        {
            switch($annotation->name)
            {
                case "_comment":
                    // Extract the first sentence of the comment for the desc.
                    $first_period_pos = mb_strpos($annotation->value, ".");
                    if ($first_period_pos)
                    {
                        // The description is the first sentence
                        $method_info["description"] =
                            substr($annotation->value, 0, $first_period_pos);
                    }
                    else
                    {
                        // The entire comment is the description
                        $method_info["description"] = $annotation->value;
                    }

                    // Store the entire comment
                    $method_info["comment"] = $annotation->value;
                    break;
                case "Path":
                    $method_info["path"][] = $annotation->value;
                    break;
                case "Methods":
                    $method_info["method"] = array_merge($method_info["method"],
                            preg_split('/\s+/u', $annotation->value));
                    if (! $method_info["method"])
                    {
                        $method_info["method"] =
                            array("GET", "POST", "PUT", "DELETE");
                    }
                    break;
                case "Resource":
                    $method_info["resource"] =
                        array_combine(array("class", "name"),
                            array_pad(preg_split('/\s+/u',
                                $annotation->value, 2), 2, ""));
                    break;
                case "Action":
                    $method_info["action"] = $annotation->value;
                    break;
                case "param":
                    $param = array_combine(array("type", "name", "description"),
                        array_pad(preg_split('/\s+/u', $annotation->value, 3),
                            3, ""));

                    if ($param["type"] == "array" ||
                        substr($param["type"], -2) == "[]")
                    {
                        $param["type"] = substr($param["type"], 0, -2);
                        $param["list"] = true;
                    }
                    else
                    {
                        $param["list"] = false;
                    }

                    if ($param["name"][0] == "$")
                    {
                        $param["name"] = substr($param["name"], 1);
                    }

                    $method_info["param"][] = $param;
                    break;
                case "GetValue":
                    $param = array_combine(array("type", "var", "param"),
                        array_pad(preg_split('/\s+/u', $annotation->value, 3),
                            3, ""));
                    if (! $param["param"])
                    {
                        $param["param"] = $param["var"];
                    }
                    $method_info["get"][] = $param;
                    break;
                case "GetQuery":
                    $method_info["query"] = true;
                    break;
                case "PostValue":
                    $param = array_combine(array("type", "var", "param"),
                        array_pad(preg_split('/\s+/u', $annotation->value, 3),
                            3, ""));
                    if (! $param["param"])
                    {
                        $param["param"] = $param["var"];
                    }
                    $method_info["post"][] = $param;
                    break;
                case "Body":
                    $method_info["body"] =
                        array_combine(array("param", "type"),
                        array_pad(preg_split('/\s+/u', $annotation->value, 2),
                            2, ""));
                    break;
                case "Authentication":
                    $method_info["authentication"] = $annotation->value;
                    break;
                case "return":
                    $method_info["return"] =
                        array_combine(array("type", "description"),
                            array_pad(preg_split('/\s+/u',
                                $annotation->value, 2), 2, ""));
                    if ($method_info["return"]["type"] == "array" ||
                        substr($method_info["return"]["type"], -2) == "[]")
                    {
                        $method_info["return"]["type"] =
                            substr($method_info["return"]["type"], 0, -2);
                        $method_info["return"]["list"] = true;
                    }
                    else
                    {
                        $method_info["return"]["list"] = false;
                    }
                    break;
            }
        }

        // Return the parsed method_info
        return $method_info;
    }

    /**
     * Extracts the resource from a method's method_info
     *
     * @history
     * 2014.04.29:
     *   (AT)  Initial implementation
     * 2014.05.29:
     *   (AT)  Added short description to the resource
     *
     * @version 2014.05.29
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $method_info
     *   Method information
     * @return \Cougar\RestService\Models\Resource Resource object
     */
    protected function extractResource(array $method_info)
    {
        // Initialize the resource variable
        $resource = null;

        if ($method_info["resource"])
        {
            // See if we have a class
            if ($method_info["resource"]["class"])
            {
                // Create the resource
                $resource = new Resource();
                $resource->class = $method_info["resource"]["class"];

                // See if we have an alternate name
                if ($method_info["resource"]["name"])
                {
                    // Set the alternate resource name
                    $resource->name = $method_info["resource"]["name"];
                }
                else
                {
                    // Set the resource name from the class name
                    $resource->name = $this->generateResourceName(
                        $method_info["resource"]["class"]);
                }
            }
        }

        // See if we had a resource
        if (! $resource)
        {
            // See if we have a return annotation
            if ($method_info["return"])
            {
                // See if the return type is a primitive
                if (! $this->isPrimitive($method_info["return"]["type"]))
                {
                    // Create the resource from the return type
                    $resource = new Resource();
                    $resource->class = $method_info["return"]["type"];
                    $resource->name = $this->generateResourceName(
                        $resource->class);
                }
            }
            else if (count($method_info["param"]) == 1 &&
                $method_info["param"][0]["type"] &&
                ! $this->isPrimitive($method_info["param"][0]["type"]))
            {
                // Get the resource name from the first and only parameter
                $resource = new Resource();
                $resource->class = $method_info["param"][0]["type"];
                $resource->name = $this->generateResourceName($resource->class);
            }
        }

        // If we still don't have a resource, create a new unnamed one
        if (! $resource)
        {
            $resource = new Resource();
            $resource->name = "Resource-" .
                (count($this->resources) + 1);
        }

        // Set the Resource ID from the name
        $resource->resourceId = $resource->name;

        // Set the short description from the class name
        if ($resource->class)
        {
            try
            {
                $class_annotations =
                    Annotations::extractFromObject($resource->class);
                foreach($class_annotations->class as $annotation)
                {
                    if ($annotation->name == "_comment")
                    {
                        // Extract the first sentence
                        $first_period_pos = mb_strpos($annotation->value, ".");
                        if ($first_period_pos)
                        {
                            // The description is the first sentence
                            $resource->shortDescription =
                                substr($annotation->value, 0,
                                    $first_period_pos);
                        }
                        else
                        {
                            // The entire comment is the description
                            $resource->shortDescription = $annotation->value;
                        }
                        break;
                    }
                }
            }
            catch (\Exception $e)
            {
                // Ignore the error
            }
        }

        # Return the resource
        return $resource;
    }

    /**
     * Extracts the actions from a method's information.
     *
     * @history
     * 2014.04.30:
     *   (AT)  Initial implementation
     * 2014.06.03:
     *   (AT)  Make sure action name is properly set when using the @Action
     *         annotation
     *
     * @version 2014.06.03
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $method_info
     *   Method method_info
     * @return \Cougar\RestService\Models\Action Actions performed by the method
     */
    protected function extractAction(array $method_info)
    {
        // Create the Action object
        $action = new Action();

        // Start populating values
        $action->shortDescription = $method_info["description"];
        $action->description = $method_info["comment"];

        // See if the action annotation is defined
        if ($method_info["action"])
        {
            $action->name = $method_info["action"];
        }
        else
        {
            // Define the action from the first method
            $method = strtolower($method_info["method"][0]);
            switch($method)
            {
                case "get":
                    if ($method_info["return"]["list"])
                    {
                        $action->name = "list";
                    }
                    else
                    {
                        $action->name = "read";
                    }
                    break;
                case "post":
                    $action->name = "create";
                    break;
                case "put":
                    $action->name = "update";
                    break;
                case "delete":
                    $action->name = "delete";
                    break;
                default:
                    $action->name = ucwords($method);
                    break;
            }
        }

        // Add the HTTP methods
        $action->httpMethods = $method_info["method"];

        // Go through each of the paths
        foreach($method_info["path"] as $path)
        {
            // Replace the : with {} and create the parameter
            $path_values = explode("/", $path);
            foreach($path_values as &$path_value)
            {
                // Skip empty parts of the path (like the first element)
                if (! $path_value)
                {
                    continue;
                }

                if ($path_value[0] == ":")
                {
                    $param_info = array_pad(explode(":", $path_value, 4),
                        4, "");

                    // Change the way the parameter is displayed in the path
                    $path_value = "{" . $param_info[1] . "}";

                    // Create the parameter object
                    $parameter = new Parameter();
                    $parameter->name = $param_info[1];

                    if ($param_info[2])
                    {
                        $parameter->type = strtolower($param_info[2]);
                    }

                    if ($param_info[3])
                    {
                        $parameter->constraint = $param_info[3];
                    }

                    // Go through the parameters and extract additional info
                    foreach($method_info["param"] as $param)
                    {
                        // See if the parameter name matches
                        if ($param["name"] == $parameter->name)
                        {
                            // Define the type
                            if ($param["type"] && ! $parameter->type)
                            {
                                $parameter->type = $param["type"];
                            }
                            else if (! $parameter->type)
                            {
                                $parameter->type = "string";
                            }

                            // Add the description
                            if ($param["description"])
                            {
                                $parameter->description = $param["description"];
                            }

                            $parameter->list = $param["list"];

                            // Break out of the loop
                            break;
                        }
                    }

                    $parameter->location = "URI";

                    // Add the parameter to the list
                    $action->parameters[] = $parameter;
                }
            }
            $path = implode("/", $path_values);

            // Add the path to the action resource
            $action->paths[] = $path;
        }

        // Parse the GET, POST and BODY parameters
        foreach($method_info["get"] as $get_param)
        {
            $parameter = new Parameter();
            $parameter->name = $get_param["var"];
            $parameter->type = $get_param["type"];

            // Go through the parameters and extract additional info
            foreach($method_info["param"] as $param)
            {
                // See if the parameter name matches
                if ($param["name"] == $parameter->name)
                {
                    // Define the type
                    if ($param["type"] && ! $parameter->type)
                    {
                        $parameter->type = $param["type"];
                    }
                    else if (! $parameter->type)
                    {
                        $parameter->type = "string";
                    }

                    // Add the description
                    if ($param["description"])
                    {
                        $parameter->description = $param["description"];
                    }

                    // Break out of the loop
                    break;
                }
            }

            $parameter->location = "GET";

            $action->parameters[] = $parameter;
        }

        // Parse the GET, POST and BODY parameters
        foreach($method_info["post"] as $post_param)
        {
            $parameter = new Parameter();
            $parameter->name = $post_param["var"];
            $parameter->type = $post_param["type"];

            // Go through the parameters and extract additional info
            foreach($method_info["param"] as $param)
            {
                // See if the parameter name matches
                if ($param["name"] == $parameter->name)
                {
                    // Define the type
                    if ($param["type"] && ! $parameter->type)
                    {
                        $parameter->type = $param["type"];
                    }
                    else if (! $parameter->type)
                    {
                        $parameter->type = "string";
                    }

                    // Add the description
                    if ($param["description"])
                    {
                        $parameter->description = $param["description"];
                    }

                    // Break out of the loop
                    break;
                }
            }

            $parameter->location = "POST";

            $action->parameters[] = $parameter;
        }

        if ($method_info["body"])
        {
            $parameter = new Parameter();
            $parameter->name = $method_info["body"]["param"];
            switch(strtolower($method_info["body"]["type"]))
            {
                case "xml":
                    $parameter->type = "XML";
                    break;
                case "object":
                case "array":
                    $parameter->type = "JSON";
                    break;
                case "php":
                    $parameter->type = "Serialized PHP object";
                    break;
                default:
                    $parameter->type = "Raw data";
                    break;
            }

            // Go through the parameters and extract additional info
            foreach($method_info["param"] as $param)
            {
                // See if the parameter name matches
                if ($param["name"] == $parameter->name)
                {
                    // Add the description
                    if ($param["description"])
                    {
                        $parameter->description = $param["description"];
                    }

                    // Break out of the loop
                    break;
                }
            }

            $parameter->location = "Body";

            $action->parameters[] = $parameter;
        }

        // Set the authentication requirement
        $action->authentication = $method_info["authentication"];

        // Parse the return value
        $action->returnValue = new ReturnValue();
        $action->returnValue->type = $this->generateResourceName(
            $method_info["return"]["type"]);
        $action->returnValue->list = $method_info["return"]["list"];
        $action->returnValue->description =
            $method_info["return"]["description"];

        // Return the action
        return $action;
    }
}
?>
