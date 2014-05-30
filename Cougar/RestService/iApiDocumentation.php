<?php

namespace Cougar\RestService;

/**
 * The API Builder creates API documentation from interfaces and classes.
 *
 * @history
 * 2014.05.12:
 *   (AT)  Initial definition
 * 2014.05.30:
 *   (AT)  Add actions parameter to getResources() to return the list of
 *         resources in one call; helps the AngularJS application search on all
 *         resource and action information
 *
 * @version 2014.05.30
 * @package Cougar
 * @license MIT
 *
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iApiDocumentation
{
    /**
     * Sets the human-readable name of the application.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial definition
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $name
     *   Application name
     */
    public function setApplicationName($name);

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
     */
    public function setApplicationDescription($description);

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
     */
    public function setUrlPrefix($url_prefix);

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
     * @Path /:prefix
     * @Methods GET
     * @XmlRootElement application
     *
     * @return \Cougar\RestService\Models\Application
     */
    public function getApplicationInformation();

    /**
     * Adds a component to the application. A component is a subset of the
     * application functionality. You must provide a interface or class name or
     * a list of interfaces and class name that are part of the component.
     *
     * The Component ID should be a human-readable name for the component, one
     * or two words without spaces and URL-safe.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial definition
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
     */
    public function addComponent($component_id, $component_name, $class_list,
        $description = null);

    /**
     * Returns the list of application components.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial definition
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * @XmlRootElement components
     * @XmlObjectName component
     *
     * @Path /:prefix/components
     * @Methods GET
     *
     * @return \Cougar\RestService\Models\Component[]
     *   List of available components
     */
    public function getComponents();

    /**
     * Returns a list of resources for the given component.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial definition
     * 2014.05.30:
     *   (AT)  Add actions parameter; allows AngularJS application to search all
     *         resource information and actions
     *
     * @version 2014.05.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @Path /:prefix/resources
     * @Path /:prefix/component/:component_id
     * @Methods GET
     * @GetValue set actions
     * @XmlRootElement resources
     * @XmlObjectName resource
     *
     * @param string $component_id
     *   Component ID
     * @param bool $actions
     *   Whether to include the resource's actions
     * @return \Cougar\RestService\Models\Resource[]
     *   List of available resources for the given component
     */
    public function getResources($component_id, $actions = false);

    /**
     * Describes the given resource. The description includes the list of
     * fields, their data types and value constraints.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial definition
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @Path /:prefix/resource/:resource_id
     * @Methods GET
     * @XmlRootElement resource
     *
     * @param string $resource_id
     *   Resource ID
     * @return \Cougar\RestService\Models\ResourceDescription
     *   Resource information
     */
    public function getResource($resource_id);

    /**
     * Returns a list of actions on the given resource.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial definition
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @Path /:prefix/resource/:resource_id/actions
     * @Methods GET
     * @XmlRootElement actions
     * @XmlObjectName action
     *
     * @param string $resource_id
     *   Resource ID
     * @return \Cougar\RestService\Models\ResourceAction[]
     *   List of actions on resource
     */
    public function getResourceActions($resource_id);

    /**
     * Returns the details for the specific action on the given resource.
     *
     * @history
     * 2014.04.15:
     *   (AT)  Initial definition
     *
     * @version 2014.04.15
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @Path /:prefix/resource/:resource_id/action/:action_id
     * @Path /:prefix/resource/:resource_id/:action_id
     * @Methods GET
     *
     * @param string $resource_id
     *   Resource ID
     * @param string $action_id
     *   Action ID
     * @return \Cougar\RestService\Models\ResourceAction
     *   Resource action information
     */
    public function getResourceAction($resource_id, $action_id);
}
?>
