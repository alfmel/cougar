<?php

namespace Cougar\Model;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The PDO Model abstract class allows programmers to easily extend Model
 * objects to map them to a relational database via PDO. PDO Models can be
 * mapped to multiple tables either through a 1:1 join, or via child objects.
 * PDO Models support basic CRUD operations (or INSERT, SELECT, UPDATE, DELETE
 * in SQL lingo). They also allow for the searching and retrieval of multiple
 * records.
 * 
 * Select operations are performed by instantiating the object with an
 * associative array or object with the values of the primary key.
 * 
 * Insert operations are performed by instantiating a new object without any
 * value parameters. You may then set any values on the property and insert the
 * record by calling the save() method.
 * 
 * Update operations are performed by instantiating the object with values,
 * similar to a Select. You may also use the inherited __import() method or
 * change properties as needed. After all changes have been made, call the
 * save() method to record the changes.
 * 
 * Delete operations are similar to the update operation, but you call the
 * delete() method instead of save().
 * 
 * List (or query) operations are done by instantiating a new object without any
 * value parameters and calling the query() method with an optional list of
 * search parameters.
 * 
 * Your PdoModel-based class must use annotations to set the necessary mappings
 * to SQL. You may set property annotations in your original Model object or
 * trait. Class annotations may be specified in the document block of your
 * PdoModel and/or base Model class or trait.
 * 
 * To create a PDO Model, extend your base Model class and use the tPdoModel
 * trait or create a new class that extends the PdoModel abstract class and
 * either add your properties directly to it, or include them by using a trait.
 * 
 * To define your PDO Model, add the following annotations to your class
 * document block:
 * 
 *   @Table tablename
 *   The table name where the record is stored
 * 
 *   @Allow [CREATE] [READ] [UPDATE] [DELETE] [QUERY]
 *   A space-delimited list of allowed CRUD and query operations. If this tag is
 *   omitted, the object will allow create, read, update and query operations.
 * 
 *   @Join expression
 *   @JOIN expression
 *   A SQL JOIN expression to join two tables together. This is useful when
 *   using traits to define your properties, since you can join two different
 *   models together with the SQL JOIN. However, the JOIN must only return one
 *   record.
 * 
 *   @PrimaryKey
 *   The property or properties that make up the Primary Key, separated by
 *   white space.
 * 
 *   @ReadOnly property [property ...]
 *   Mark the given properties as read-only
 * 
 *   @DeleteFlag property value
 *   When issuing a delete operation, set the the given property to the given
 *   value instead of issuing a DELETE statement. Useful for things that are
 *   not actually deleted.
 * 
 *   @QueryList property [property ...]
 *   Allow only the given properties to be queried when using the query() static
 *   method. If not specified, all properties can be queried, which may not be
 *   efficient on the database side.
 * 
 *   @QueryView view
 *   Use the given view to determine which properties to include in the list
 *   view.
 *
 *   @QueryUnique
 *   When performing a query, only return unique values.
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
 *   Optional annotation to turn off record caching. If this annotation is used,
 *   the cache prefix may be omitted. This option will also turn off query
 *   caching.
 * 
 *   @NoQueryCache
 *   Optional annotation to turn off query caching. For tables with a lot of
 *   changing data you may wish to cache the individual records, but not queries
 *   since the changes will invalidate the entries before they are used. This
 *   annotation will turn off caching 
 * 
 *   ** NOTE: for the moment, query caching is disabled since the Cache object
 *      doesn't support the clearing of grouped entries yet.
 * 
 * To define the columns in your record, define each one of them as a public
 * property. Then add the any of the following optional annotations to the
 * property document block:
 * 
 *   @Column name
 *   If your property name does not correspond to the column name in the
 *   database, you may specify the actual column name here. The column name
 *   will also create an alias for the property.
 *
 *   @Unbound
 *   If this annotation is set, the property will not be considered part of the
 *   database table. Therefore, it will not be queried or updated. This is
 *   useful when you have properties whose values are calculated in
 *   __postValidate() from the values in the bound columns.
 * 
 *   The PdoModel class extends the Model class, so all options and features
 *   in the Model class are automatically inherited.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2013.11.25:
 *   (AT)  Implement iPersistentModel (new name for iStoredModel)
 * 2014.02.18:
 *   (AT)  Add support for unbound properties
 *
 * @version 2014.02.18
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
abstract class PdoModel implements iPersistentModel
{
    use tPdoModel;
}
?>
