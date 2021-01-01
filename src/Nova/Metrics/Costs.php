<?php

namespace Zareismail\Bonchaq\Nova\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest; 

class Costs extends MetricValue
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->sum($request, $this->newCostQuery($request), 'amount');
    }
}
