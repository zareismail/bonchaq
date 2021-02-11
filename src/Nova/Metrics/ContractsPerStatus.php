<?php

namespace Zareismail\Bonchaq\Nova\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Zareismail\Bonchaq\Models\BonchaqContract;

class ContractsPerStatus extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, BonchaqContract::class, 'end_date');
    }

    /**
     * Format the aggregate result for the partition.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $result
     * @param  string  $groupBy
     * @return array
     */
    protected function formatAggregateResult($result, $groupBy)
    {
        $key = $result->{last(explode('.', $groupBy))};

        return [strval($key) => $result->aggregate];
    }

    /**
     * Create a new partition metric result.
     *
     * @param  array  $value
     * @return \Laravel\Nova\Metrics\PartitionResult
     */
    public function result(array $value)
    {
        return parent::result( collect($value)->groupBy(function($value, $key) {
            return now()->gt($key) ? __('Expired Contracts') : __('Live Contracts');
        })->map->sum()->all());
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
        return 'contracts-per-status';
    }
}
