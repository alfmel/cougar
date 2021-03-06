<?php

namespace Cougar\RestService\Models;

use Cougar\Model\Struct;

/**
 * Provides basic information about the application
 */
class Application extends Struct
{
    /**
     * @var string Application name
     */
    public $name;

    /**
     * @var string Application description
     */
    public $description;

    /**
     * @var string URL prefix
     */
    public $urlPrefix;

    /**
     * Return the api_documentation.html file to bootstrap the AngularJS-based
     * documentation application.
     */
    public function __toHtml()
    {
        return file_get_contents(__DIR__ . "/../html/api_documentation.html");
    }
}
?>
