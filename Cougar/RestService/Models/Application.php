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
}
?>
