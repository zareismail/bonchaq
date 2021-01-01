<?php

namespace Zareismail\Bonchaq\Nova\Metrics;
 
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Http\Requests\NovaRequest;
use Zareismail\Bonchaq\Models\BonchaqMaturity;
use Zareismail\Bonchaq\Helper as BonchaqHelper;
use Zareismail\NovaPolicy\Helper;

abstract class MetricValue extends Value
{   
    /**
     * Build new query for the metrics.
     * 
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request 
     * @return \Illuminate\Database\Eloquent\Builder               
     */
    public function newCostQuery(NovaRequest $request)
    { 
        return BonchaqMaturity::whereHas('contract', function($query) {
            $query->authenticate();
        });
        
    }

    /**
     * Build new query for the metrics.
     * 
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request 
     * @return \Illuminate\Database\Eloquent\Builder               
     */
    public function newRevenueQuery(NovaRequest $request)
    {
        return BonchaqMaturity::whereHas('contract', function($query) {
            $resources = BonchaqHelper::contractables()->map(function($resource) {
                return $resource::$model;
            })->all();

            $query->whereHasMorph('contractable', $resources, function($query, $type) {
                return $query->when(Helper::isOwnable($type), function ($query) {
                    $query->authenticate();
                });
            });
        });
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30 => '30 Days',
            60 => '60 Days',
            365 => '365 Days',
            'TODAY' => 'Today',
            'MTD' => 'Month To Date',
            'QTD' => 'Quarter To Date',
            'YTD' => 'Year To Date',
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'maintainable-'.parent::uriKey();
    }
}
