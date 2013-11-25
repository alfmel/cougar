<?php

namespace Cougar\Model;

# Initialize the framework
require_once("cougar.php");

/**
 * The WS Model trait and class allows programmers to easily extend Model
 * objects and map them to REST web service resources. These resources can be
 * retrieved one at a time or queried to obtain a list of records.
 * 
 * Read operations are performed by instantiating the object with an
 * associative array or object with the identifiers for the resource.
 * 
 * Insert operations are performed by instantiating a new object without any
 * value parameters. You may then set any values on the property and create the
 * record by calling the save() method.
 * 
 * Update operations are performed by instantiating the object with values,
 * similar to a Read. You may also use the inherited __import() method or
 * change properties as needed. After all changes have been made, call the
 * save() method to save the changes.
 * 
 * Delete operations are similar to the update operation, but you call the
 * delete() method instead of save().
 * 
 * List (or query) operations are done by instantiating a new object without any
 * value parameters and calling the query() method with an optional list of
 * search parameters.
 * 
 * Your WsModel-based class must use annotations to set the necessary mappings
 * to the REST resource. You may set property annotations in your original Model
 * object or trait. Class annotations may be specified in the document block of
 * your WsModel and/or base Model class or trait.
 * 
 * To create a WS Model, extend your base Model class and use the tWsModel
 * trait or create a new class that extends the WsModel abstarct class and
 * either add your properties directly to it, or include them by using a trait.
 * 
 * To define your WS Model mappings, add the following annotations to your class
 * document block:
 * 
 *   @Allow [CREATE] [READ] [UPDATE] [DELETE] [QUERY]
 *   A space-delimited list of allowed CRUD and query operations. If this tag is
 *   omitted, the object will allow create, read, update and query operations.
 * 
 *   @BaseUri environment uri
 *   The base URI for the resource in the given environment. This annotation may
 *   be repeated as many times as needed for each environment. The URI may
 *   specify property name values that need to be added to the URI by prefixing
 *   them with a colon. For example:
 * 
 *     https://api.somewehre.com/path/to/resource/:parentId
 * 
 * 
 *   @Create http_method [uri]
 *   @Read http_method [uri]
 *   @Update http_method [uri]
 *   @Delete http_method [uri]
 *   @Query http_method [uri]
 *   Each of these annotations defines the HTTP method and URI that will be used
 *   for each one of the CRUD operations. The HTTP method is usually one of GET,
 *   POST, PUT and DELETE. The URI will be appended to the BaseUri. For the
 *   BaseUri above setting the annotation's's uri to /:resourceId will make a
 *   REST call to:
 * 
 *     https://api.somewehre.com/path/to/resource/:parentId/:resourceId
 * 
 * 
 *   @CreateGetFields property1 [property2 [...]]
 *   @ReadGetFields property1 [property2 [...]]
 *   @UpdateGetFields property1 [property2 [...]]
 *   @DeleteGetFields property1 [property2 [...]]
 *   @QueryGetFields property1 [property2 [...]]
 *   Include the given properties as GET parameters to the call. You may use the
 *   special __object__ property name to include all object properties. Adding
 *   this annotation with a value of property1 with the above examples would
 *   append ?property1=value to the URI.
 * 
 *   @CreatePostFields property1 [property2 [...]]
 *   @ReadPostFields property1 [property2 [...]]
 *   @UpdatePostFields property1 [property2 [...]]
 *   @DeletePostFields property1 [property2 [...]]
 *   @QueryPostFields property1 [property2 [...]]
 *   Same as [CRUD]Get, except that the property will be added as a POST
 *   variable and sent in tye body. You may also use the special __object__
 *   property to send the entire object.
 * 
 *   @CreateBody XML|JSON|PHP
 *   @ReadBody XML|JSON|PHP
 *   @UpdateBody XML|JSON|PHP
 *   @DeleteBody XML|JSON|PHP
 *   @QueryBody XML|JSON|PHP
 *   Convert the object to XML or JSON and send as the body of the HTTP request.
 *   The appropriate XML or JSON content type will be set.
 * 
 *   @ResponseType XML|JSON|PHP
 *   The expected data type of the WS response
 * 
 *   @ResourceID property [property ...]
 *   Properties that constitude the unique identifier for the resource (similar
 *   to the primary key in a relational database)
 * 
 *   @ReadOnly property [property ...]
 *   Mark the given properties as read-only
 * 
 *   @QueryList property [property ...]
 *   Allow only the given properties to be queried when using the query() static
 *   method. If not specified, all properties can be queried, which may not be
 *   efficient on the databse side.
 * 
 *   @QueryView view
 *   Use the given view to determine which properties to include in the list
 *   view.
 * 
 *   @NoQuery
 *   Disable the ability to query records
 * 
 *   @CachePrefix prefix
 *   The cache prefix value. The prefix will be appended with the values of the
 *   primary key when stored in the cache (default: byu.record).
 * 
 *   @CacheTime
 *   Cache time-to-live in seconds (default: 3600 [1 hour]).
 * 
 *   @VoidCacheEntry cache_key
 *   If this record is modified, void the cache entry specified. You may use
 *   colons to substitute properties from the object.
 * 
 *   @NoCache
 *   Optional annotation to turn off data caching. If this annotation is used,
 *   the cache prefix may be omitted.
 * 
 *   @View view
 *   Use the given view when exporting individual properties or the entire
 *   object.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2013.11.25:
 *   (AT)  Implement iPersistentModel (new name for iStoredModel)
 *
 * @version 2013.11.25
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
abstract class WsModel implements iPersistentModel
{
    use tWsModel;
}
?>
