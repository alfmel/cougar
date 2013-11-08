# COUGAR FRAMEWORK
----------

The Cougar Framework is an object-oriented application framework for PHP 5.4 or
above. Cougar aims to simplify the development of RESTful APIs by integrating,
simplifying and optimizing security, URI routing and database access.

Cougar is not a general-purpose framework. It only focuses on making it easier
to create REST Web Services. It does not have shopping carts, templeting, or
much in the way of an interface.

Cougar promotes Inversion of Control (dependency injection) and Attribute-
Oriented programming via annotations. For example, to publish a class method as
a web service end point, you would write:

```php
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
```

and then plumb it together with a REST request handler as follows:

```php
  $my_class = new MyClass();

  $security = new Cougar\Security\Security();
  $rest_service = new Cougar\RestService\AnnotatedRestService($security);
  $rest_service->bindFromObject($my_class);
  $rest_service->handleRequest();
```

As you can see, Cougar allows you to publish your API via REST in as little as
6 lines of code!

Cougar has been inspired by many similar micro and full frameworks such as
Spring, Slim, Doctrine, Tonic and others.

## Examples

The ZF2 Album tutorial can be written in Cougar as a REST API in 150 lines of
code, complete with database connectivity, transaction control and hierarchical
models. To see the code, go to the
[zend_tutorial_in_cougar.php](https://github.com/alfmel/zend_tutorial_in_cougar).

## Tutorial

We have published a [tutorial](https://github.com/alfmel/cougar_tutorial/wiki)
that walks you through the development of a Cougar-based application.

## Installing Cougar

The easiest way to install Cougar for use is via Pear:

```bash
  pear channel-discover alfmel.github.com/pear
  pear install cougar/cougar
```

To develop Cougar, clone from github and follow the instructions in INSTALL.txt:

```bash
  git clone https://github.com/alfmel/cougar
```

