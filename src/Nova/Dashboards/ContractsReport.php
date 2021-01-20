<?php

namespace Zareismail\Bonchaq\Nova\Dashboards;

use Illuminate\Support\Str;
use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
use Laravel\Nova\Dashboard;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\{Select, DateTime};
use Laravel\Nova\Nova;
use Coroowicaksono\ChartJsIntegration\LineChart;
use Zareismail\Bonchaq\Nova\Maturity;
use Zareismail\Bonchaq\Nova\Subject; 
use Zareismail\Bonchaq\Helper; 
use Zareismail\Fields\Contracts\Cascade;

class ContractsReport extends Dashboard
{
    use ConditionallyLoadsAttributes;

    /**
     * Get the displayable name of the dashboard.
     *
     * @return string
     */
    public static function label()
    {
        return __('Contracts Report');
    }

    public function filters(NovaRequest $request)
    { 
        return $this->filter([  
            Select::make(__('Which Reprot'), 'revenue') 
                ->options([
                    'expenditures' => __('Expenditures'),
                    'revenue' => __('Revenue'),
                ])
                ->displayUsingLabels()
                ->default('revenue')
                ->withMeta([
                    'width' => 'w-1/5'
                ]),  

            DateTime::make(__('From Date'), 'from_date', function($value) {
                if(! is_null($value)) {
                    return \Carbon\Carbon::create($value)->format('Y-m-d H:i:s.u'); 
                }
            })  
                ->nullable()
                ->help($request->filled('from_date') ? __('Filtered by :date', [
                    'date' => $request->get('from_date')
                ]) : '')
                ->withMeta([
                    'width' => 'w-2/5', 
                    'placeholder' => $request->get('to_date')
                ]),

            DateTime::make(__('To Date'), 'to_date', function($value) {
                if(! is_null($value)) {
                    return \Carbon\Carbon::create($value)->format('Y-m-d H:i:s.u'); 
                }
            })  
                ->nullable()
                ->help($request->filled('to_date') ? __('Filtered by :date', [
                    'date' => $request->get('to_date')
                ]) : '')
                ->withMeta([
                    'width' => 'w-2/5', 
                    'placeholder' => $request->get('to_date')
                ]),

            Select::make(__('Report Of'), 'contractable') 
                ->options(Helper::contractables()->mapWithKeys(function($resource) {
                    return [
                        $resource::uriKey() => $resource::label()
                    ];
                }))
                ->displayUsingLabels()
                ->nullable()
                ->withMeta([
                    'placeholder' => __('All')
                ]), 

            $this->mergeWhen($request->filled('contractable'), function() use ($request) {
                $resource = $this->findResourceForKey($request->get('contractable'));

                return (array) $this->getFieldsForResource($request, $resource); 
            }), 
        ]);
    }

    public function findResourceForKey($key)
    {
        return Helper::contractables()->first(function($resource) use ($key) {
            return $resource::uriKey() == $key;
        });
    }

    public function getFieldsForResource($request, $resource)
    {
        $fields = []; 
        $viaResourceId = null;

        if($parent = $this->findParentForResource($resource)) {
            $fields = array_merge($fields, $this->getFieldsForResource(
                $request, $parent
            ));  
        }
        
        if(! is_null($parent) && ! $request->filled($this->resourceFilterKey($parent))) {
            return $fields;
        } elseif(! is_null($parent)) {
            $viaResourceId = intval($request->get($this->resourceFilterKey($parent)));
        }

        $selection = tap($this->getResourceSelection($request, $resource, $viaResourceId), function($field) {
            $contractable = $this->findResourceForKey(request('contractable'));

            if($field->attribute == $this->resourceFilterKey($contractable)) {
                $field->nullable()->withMeta([
                    'placeholder' => __('All') 
                ]); 
            }
        });  

        array_push($fields, $selection); 

        return $fields;
    }

    /**
     * Get the parent resource of the given resource.
     * 
     * @param  string $resource 
     * @return string           
     */
    public function findParentForResource($resource)
    {
        if($resource::newModel() instanceof Cascade) {
            return Nova::resourceForModel($resource::newModel()->parent()->getModel());
        }  
    }

    /**
     * Get Resoruce item selction.
     * 
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request       
     * @param  string $resource      
     * @param  string $viaResourceId 
     * @return \LaravelNova\Fields\Field                
     */
    public function getResourceSelection($request, $resource, $viaResourceId)
    {
        return Select::make($resource::label(), $this->resourceFilterKey($resource)) 
                ->options($resource::newModel()->when($viaResourceId, function($query) use ($viaResourceId) {
                    return $query->whereHas('parent', function($query) use ($viaResourceId) {
                        $query->whereKey($viaResourceId);
                    });
                })->get()->keyBy('id')->mapInto($resource)->map->title())
                ->displayUsingLabels();
    }

    public function resourceFilterKey($resource)
    {
        return $resource::uriKey();
    }

    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    { 
        return Subject::newModel()->with([
            'contracts' => function($query) {
                $query
                    ->when(request()->input('revenue') === 'expenditures', function($query) {
                        $query->authenticate();
                    }) 
                    ->with([
                        'maturities' => function($query) {
                            $query ->when(request()->filled('from_date'), function($query) {
                                $query->where('payment_date', '>=', request()->get('from_date'));
                            })
                            ->when(request()->filled('to_date'), function($query) {
                                $query->where('payment_date', '<=', request()->get('to_date'));
                            });
                        }
                    ])
                    ->when(request('contractable'), function($query) {
                        $resource = Nova::resourceForKey(request('contractable'));
                        $queryCallback = function($query) use ($resource) {      
                            $query->when(
                                request()->filled($this->resourceFilterKey($resource)), 
                                function($query) use ($resource) {
                                    $query->whereKey(request()->input($this->resourceFilterKey($resource)));
                                }, 
                                function($query) use ($resource) {
                                    if($parent = $this->findParentForResource($resource)) {
                                        $query->whereHas('parent', function($query) use ($parent) {
                                            $query->whereKey(request()->input($this->resourceFilterKey(
                                                $parent
                                            )));
                                        });
                                    }
                                }
                            )->when(request()->input('revenue') === 'revenue', function($query) {
                                $query->authenticate();
                            });
                        };

                        $query->whereHasMorph(
                            'contractable', [$resource::newModel()->getMorphClass()], $queryCallback
                        ); 
                    });
            }
        ])->get()->flatMap(function($subject) {
            $balance = $subject->contracts->sum('amount');

            return [
                (new LineChart())
                    ->title($subject->name.PHP_EOL.config('nova.currency'))
                    ->animations([
                        'enabled' => true,
                        'easing' => 'easeinout',
                    ])
                    ->series(array([
                        'barPercentage' => 0.5,
                        'label' => __('Payment'),
                        'borderColor' => '#f7a35c',
                        'data' => collect($this->months())->map(function($month) use ($subject) {
                            return $subject->contracts->flatMap->maturities->filter(function($maturity) use ($month) { 
                                return $maturity->payment_date->format($this->dateFormat()) === $month;
                            })->sum('amount');
                        })->all(),
                    ],[
                        'barPercentage' => 0.5,
                        'label' => __('Balance'),
                        'borderColor' => '#90ed7d',
                        'data' => collect($this->months())->map(function($month, $key) use ($balance) {
                            return $balance;
                        })->all(),
                    ]))
                    ->options([
                        'xaxis' => [
                            'categories' => $this->months()
                        ], 
                    ])
                    ->width('full')
                    ->withMeta([
                        'uriKey' => $subject->name
                    ]),

                (new LineChart())
                    ->title($subject->name.PHP_EOL.__('Aggregate').PHP_EOL.config('nova.currency'))
                    ->animations([
                        'enabled' => true,
                        'easing' => 'easeinout',
                    ])
                    ->series(array([
                        'barPercentage' => 0.5,
                        'label' => __('Payment'),
                        'borderColor' => '#f7a35c',
                        'data' => collect($this->months())->map(function($month, $key) use ($subject) {
                            return $subject->contracts->flatMap->maturities->filter(function($maturity) use ($key) { 
                                return $maturity->payment_date->lte(now()->addMonths($key));
                            })->sum('amount');
                        })->all(),
                    ],[
                        'barPercentage' => 0.5,
                        'label' => __('Balance'),
                        'borderColor' => '#90ed7d',
                        'data' => collect($this->months())->map(function($month, $key) use ($balance) {
                            return $balance * ($key + 1);
                        })->all(),
                    ]))
                    ->options([
                        'xaxis' => [
                            'categories' => $this->months()
                        ],
                    ])
                    ->width('full')
                    ->withMeta([
                        'uriKey' => $subject->name.'-Aggregated'
                    ]),
            ];
        }); 
    }

    /**
     * Returns an array of the year months.
     * 
     * @return array
     */
    public function months()
    {
        return [
            now()->format($this->dateFormat()),
            now()->addMonths(1)->format($this->dateFormat()),
            now()->addMonths(2)->format($this->dateFormat()),
            now()->addMonths(3)->format($this->dateFormat()),
            now()->addMonths(4)->format($this->dateFormat()),
            now()->addMonths(5)->format($this->dateFormat()),
            now()->addMonths(6)->format($this->dateFormat()),
            now()->addMonths(7)->format($this->dateFormat()),
            now()->addMonths(8)->format($this->dateFormat()),
            now()->addMonths(9)->format($this->dateFormat()),
            now()->addMonths(10)->format($this->dateFormat()),
            now()->addMonths(11)->format($this->dateFormat()),
        ];
    }

    /**
     * Returns the month format string.
     * 
     * @return string
     */
    public function dateFormat()
    {
        return 'M';
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'bonchaq-contracts-report';
    }
}