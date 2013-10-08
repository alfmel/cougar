COUGAR FRAMEWORK
================

The Cougar Framework is an object-oriented application framework for PHP 5.4 or
above. Cougar aims to simplify the development of RESTful APIs by integrating,
simplifying and optimizing security, URI routing and database access.

Cougar is not a general-purpose framework. It only focuses on making it easier
to create REST Web Services. It does not have shopping carts, templeting, or
much in the way of an interface.

Cougar promotes Inversion of Control (dependency injection) and Attribute-
Oriented programming via annotations. For example, to publish a class method as
a web service end point, you would write:

  class MyClass
  {
    /**
     * @Path /resource/:id
     * @Methods GET
     */
    public function myMethod($id)
    {
      // Your code here
    }
  }

and then plumb it together with a REST request handler as follows:

  $my_class = new MyClass();
  $rest_service = new Cougar\RestService\AnnotatedRestService();
  $rest_service->bindFromObject($my_class);
  $rest_service->handleRequest();

As you can see, Cougar allows you to publish your API via REST with less than 10
lines of code! The ZF2 Album tutorial can be written in its entirety in Cougar
in about 125 lines of code, complete with database connectivity, transaction
control and hierarchical models. To see the code, go to:

  https://github.com/alfmel/zend_tutorial_in_cougar
