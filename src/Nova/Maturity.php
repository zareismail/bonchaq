<?php

namespace Zareismail\Bonchaq\Nova; 

use Illuminate\Http\Request; 
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\{ID, Text, Number, Currency, DateTime, BelongsTo}; 
use DmitryBubyakin\NovaMedialibraryField\Fields\Medialibrary; 
use Zareismail\NovaContracts\Nova\User; 

class Maturity extends Resource
{  
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Zareismail\Bonchaq\Models\BonchaqMaturity::class;

    /**
     * The number of resources to show per page via relationships.
     *
     * @var int
     */
    public static $perPageViaRelationship = 12;

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['auth'];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'tracking_code'
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

            BelongsTo::make(__('Contract'), 'contract', Contract::class)
                ->withoutTrashed(),  

            BelongsTo::make(__('Payer'), 'auth', User::class)
                ->withoutTrashed() 
                ->searchable()
                ->debounce(100)
                ->readonly(! is_null($contract = $this->contract ?: $request->findParentModel()) && $request->isMethod('get'))
                ->default($this->auth_id ?? $contract->auth_id ?? $request->user()->id)
                ->canSee(function($request) {
                    return $request->user()->can('update', static::newModel());
                }), 

            Number::make(__('Due'), 'installment')
                ->sortable()
                ->exceptOnForms(),

            $this->mergeWhen(! $request->isUpdateOrUpdateAttachedRequest() && $contract, function() use ($contract) {
                return [ 
                    Currency::make(__('Installment Amount'), 'amount')
                        ->required()
                        ->rules('required')
                        ->readonly()
                        ->hideFromIndex()
                        ->withMeta([
                            'value' => $contract->amount
                        ]), 

                    Currency::make(__('Payments Total'), function() use ($contract) {
                        return $contract->maturities->between(null, $this->installment)->totalPayment();
                    }),   

                    Currency::make(__('Total Lacks'), function() use ($contract) {
                        $sum = $contract->maturities->between(null, $this->installment)->totalPayment();

                        return $contract->installments * $contract->amount - $sum;
                    }),  

                    Currency::make(__('Deficit'), function($amount) use ($contract) {
                        return $this->amount ? $contract->amount - $this->amount : 0;
                    }),  
                ];
            }),

            Currency::make(__('Balance'), 'amount') 
                ->readonly()
                ->onlyOnForms()
                ->withMeta([
                    'value' => optional($this->contract)->amount,
                ]),

            Currency::make(__('Payment'), 'amount')
                ->required()
                ->rules('required')
                ->sortable(), 

            Text::make(__('Payment Tracking Code'), 'tracking_code')
                ->required()
                ->rules('required')
                ->sortable(), 

            DateTime::make(__('Payment Date'), 'payment_date')
                ->required()
                ->rules('required')
                ->sortable()
                ->exceptOnForms(), 

            Medialibrary::make(__('Attachments'), 'attachments')
                ->autouploading()
                ->hideFromIndex()
                ->nullable(),
    	];
    } 

    /**
     * Authenticate the query for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function authenticateQuery(NovaRequest $request, $query)
    {
        return $query->where(function($query) use ($request) {
            $query->when(static::shouldAuthenticate($request, $query), function($query) {
                $query->authenticate()->orWhereHas('contract', function($query) { 
                    Contract::buildIndexQuery(app(NovaRequest::class), $query);
                });
            });
        });
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
        return $query->with(['contract' => function($query) use ($request) { 
            Contract::buildIndexQuery($request, $query->withTrashed());
        }]);
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        $contract = new Contract($this->contract ?? Contract::newModel());

        return __('Due :number [:contract]', [
            'number' => $this->installment,
            'contract' => $contract->title(),
        ]);
    }

    /**
     * Apply any applicable orderings to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $orderings
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applyOrderings($query, array $orderings)
    {
        return parent::applyOrderings($query, $orderings ?: ['payment_date' => 'asc']);
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function availableForNavigation(Request $request)
    {
        return false;
    }

    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Record A Payment');
    } 

    /**
     * Determine if the current user can create new resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * Determine if the current user can create new resources or throw an exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public static function authorizeToCreate(Request $request)
    {
        return false;
    } 
}