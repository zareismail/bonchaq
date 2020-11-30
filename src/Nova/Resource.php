<?php

namespace Zareismail\Bonchaq\Nova;

use Zareismail\NovaContracts\Nova\Resource as BaseResource;

abstract class Resource extends BaseResource
{ 
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Bonchaq'; 

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
    ];
}