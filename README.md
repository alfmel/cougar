# COUGAR FRAMEWORK
----------

## IMPORTANT NOTE
The Cougar Framework is dead. I no longer work for the employer who commissioned
it, my old employer has not submitted any bugs or pull requests, and I am not
interested in maintaining it.

I will leave the code the code here for the time being. If you are interested in
the codebase, please **FORK NOW**.

## END OF IMPORTANT NOTE

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

The easiest way to install Cougar is through Composer. In your project's
directory, add a composer.json file:

```json
{
    "require": {
        "alfmel/cougar": "dev-master"
    }
}
```

Then have composer install it for you:

```bash
composer.phar install
```

Cougar will be installed in the vendor directly.

Don't let the dev-master version scare you away. Cougar is developed using
continuous delivery. That means it only receives small, incremental changes.
Every release is tested and ready for production use.

You may also try installing via PEAR. However, PEAR is deprecated and does not
contain the latest changes:

```bash
  pear channel-discover alfmel.github.com/pear
  pear install cougar/cougar
```

You may also clone from github and follow the instructions in INSTALL.txt:

```bash
  git clone https://github.com/alfmel/cougar
```

