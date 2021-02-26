<?php

namespace Zareismail\Bonchaq\Nova; 

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\{Nova, TrashedStatus}; 
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\{ID, Stack, Number, Select, Currency, DateTime, BelongsTo, HasMany}; 
use DmitryBubyakin\NovaMedialibraryField\Fields\Medialibrary;
use Zareismail\NovaContracts\Nova\User;
use Zareismail\Fields\MorphTo;  
use Armincms\Fields\Chain;  
use Zareismail\Bonchaq\Helper; 

class Contract extends Resource
{  
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Zareismail\Bonchaq\Models\BonchaqContract::class;

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['subject', 'auth'];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'contractable_type', 'period'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
    	return [
    		ID::make(), 

            BelongsTo::make(__('Payer'), 'auth', User::class)
                ->withoutTrashed() 
                ->searchable()
                ->debounce(100),  

            BelongsTo::make(__('Contract Subject'), 'subject', Subject::class)
                ->withoutTrashed(),

            MorphTo::make(__('Register For'), 'contractable')
                ->types(Helper::contractables()->all())
                ->withoutTrashed()
                // ->searchable()
                ->debounce(100), 

            Chain::make('periods', function() {
                return [ 
                    Select::make(__('Payment Period'), 'period')
                        ->options(Helper::periods())
                        ->default(Helper::MONTHLY)
                        ->required()
                        ->rules('required'),
                ];
            }),

            Chain::with('periods', function($request) {
                switch ($request->get('period', $this->period)) {
                    case Helper::DAILY:
                        return [
                            Select::make(__('Which Hour'), 'maturity')
                                ->options(array_combine(range(1, 24), range(1, 24)))
                                ->required()
                                ->rules('required'),
                        ];
                        break;

                    case Helper::WEEKLY:
                        return [
                            Select::make(__('Which Day'), 'maturity')
                                ->options(Helper::getDays())
                                ->default(Carbon::getWeekStartsAt())
                                ->required()
                                ->rules('required'),
                        ];
                        break;

                    case Helper::MONTHLY:
                        return [
                            Select::make(__('Which Day'), 'maturity')
                                ->options(array_combine(range(1, 30), range(1, 30)))
                                ->required()
                                ->rules('required'),
                        ];
                        break;

                    case Helper::YEARLY:
                        return [
                            Select::make(__('Which Month'), 'maturity')
                                ->options(Helper::getMonths())
                                ->required()
                                ->rules('required'),
                        ];
                        break;
                    
                    default:
                    return [];
                        break;
                } 
            }),

            Number::make(__('Number of installments'), 'installments')
                ->rules('required')
                ->required()
                ->default(1)
                ->min(1),

            Currency::make(__('Deposit Amount'), 'advance_payment')
                ->required()
                ->rules('required')
                ->default(0.00), 

            Currency::make(__('Installment amount'), 'amount')
                ->required()
                ->rules('required')
                ->default(0.00), 

            DateTime::make(__('Start Date'), 'start_date')
                ->required()
                ->rules('required')
                ->hideFromIndex()
                ->default(now()), 

            DateTime::make(__('End Date'), 'end_date')
                ->required()
                ->rules('required')
                ->onlyOnDetail(), 

            Stack::make(__('Period'), [
                function() {
                    return __('From:');
                },

                DateTime::make(__('Start Date'), 'start_date'),

                function() {
                    return __('Until:');
                },

                DateTime::make(__('End Date'), 'end_date'),
            ])->onlyOnIndex(), 

            Medialibrary::make(__('Attachments'), 'attachments')
                ->autouploading()
                ->hideFromIndex()
                ->nullable(),

            HasMany::make(__('Maturities'), 'maturities', Maturity::class),
    	];
    } 

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $search
     * @param  array  $filters
     * @param  array  $orderings
     * @param  string  $withTrashed
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function buildIndexQuery(NovaRequest $request, $query, $search = null,
                                      array $filters = [], array $orderings = [],
                                      $withTrashed = TrashedStatus::DEFAULT)
    { 
        $queryCallback = function($query) {
            $query->orWhereHasMorph('contractable', Helper::morphs(), function($query, $type) { 
                forward_static_call(
                    [Nova::resourceForModel($type), 'buildIndexQuery'], app(NovaRequest::class), $query
                );
            });
        };

        return parent::buildIndexQuery($request, $query, $search, $filters, $orderings, $withTrashed)
                ->when(static::shouldAuthenticate($request, $query), $queryCallback);
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return parent::indexQuery($request, $query)->tap(function($query) use ($request) { 
            $query->with('contractable', function($morphTo) {
                return $morphTo->morphWith(Helper::morphs());
            });
        });
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->subjectTitle() .':'. $this->contractableTitle(); 
    }

    /**
     * Get the subject value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function subjectTitle()
    {
        if(is_null($this->subject)) {
            return __('Subject :resource', [
                'resource' => $this->subject_id
            ]);
        }

        return with(new Subject($this->subject), function($resource) {
            return $resource->title();
        }); 
    }

    /**
     * Get the contractable value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function contractableTitle()
    {  
        return with(Nova::resourceForModel($this->contractable), function($resource) {
            if(is_null($resource)) {
                return __('Contractable : resource', [
                    'resource' => $this->contractable_type,
                ]);
            }

            return with(new $resource($this->contractable), function($resource) {
                return $resource->title();
            });
        }); 
    }

    /**
     * Get the cards available on the entity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            Metrics\ContractsPerStatus::make()->width('1/3'), 

            Metrics\Costs::make()->width('1/3'), 

            Metrics\Revenues::make()->width('1/3'),
        ];
    }

    /**
     * Get the filters available on the entity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            Filters\MinAmount::make(),
            Filters\MaxAmount::make(),
            Filters\Period::make(),
            Filters\Subject::make(), 
            Filters\Resource::make(), 
        ];
    }

    /**
     * Apply the search query to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applySearch($query, $search)
    { 
        return parent::applySearch($query, $search)->orWhere(function($query) use ($search) {
            $query->orWhereHasMorph('contractable', Helper::morphs(), function($morphTo, $type) use ($search) {
                $morphTo->where(function($query) use ($type, $search) { 
                    forward_static_call(
                        [Nova::resourceForModel($type), 'buildIndexQuery'], app(NovaRequest::class), $query, $search
                    ); 
                });
            }); 
        });
    }
    
    /**
     * Determine if the current user can view the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ability
     * @return bool
     */
    public function authorizedTo(Request $request, $ability)
    { 
        return parent::authorizedTo($request, $ability) ||
                $request->user()->can($ability, optional($this->resource)->contractable);
    }
}
