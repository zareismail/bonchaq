<?php

namespace Zareismail\Bonchaq\Nova; 

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Nova; 
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
                ->debounce(100)
                ->canSee(function($request) {
                    return $request->user()->can('forceDelete', static::newModel());
                }),  

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
                ];
            }),

            Chain::with('periods', function($request) {
                switch ($request->get('period', $this->period)) {
                    case Helper::DAILY:
                        return [
                            Select::make(__('Which Hour'), 'maturity')
                                ->options(array_combine(range(1, 24), range(1, 24))),
                        ];
                        break;

                    case Helper::WEEKLY:
                        return [
                            Select::make(__('Which Day'), 'maturity')
                                ->options(Helper::getDays())
                                ->default(Carbon::getWeekStartsAt()),
                        ];
                        break;

                    case Helper::MONTHLY:
                        return [
                            Select::make(__('Which Day'), 'maturity')
                                ->options(array_combine(range(1, 30), range(1, 30))),
                        ];
                        break;

                    case Helper::YEARLY:
                        return [
                            Select::make(__('Which Month'), 'maturity')
                                ->options(Helper::getMonths()),
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

            Currency::make(__('Advance Payment'), 'advance_payment')
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
                ->hideFromIndex(), 

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

                DateTime::make(__('Start Date'), 'start_date'),
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
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->tap(function($query) {
            $query->with('contractable', function($morphTo) {
                return $morphTo->morphWith(Helper::contractables()->map(function($resource) {
                    return $resource::$model;
                })->all());
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
            return with(new $resource($this->contractable), function($resource) {
                return $resource->title();
            });
        }); 
    }
}