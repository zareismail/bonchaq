<?php

namespace Zareismail\Bonchaq\Nova\Filters;

use Illuminate\Http\Request;
use OptimistDigital\NovaInputFilter\InputFilter;

class MinAmount extends InputFilter
{  
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {  
        return $query->where('amount', '>=', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    { 
        return [ 
        ];
    }
}
