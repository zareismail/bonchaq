<?php

namespace Zareismail\Bonchaq\Nova; 

use Illuminate\Http\Request; 
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
    public static $with = ['contract', 'auth'];

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

                    Currency::make(__('Payments Total'), 'amount')
                        ->required()
                        ->rules('required')
                        ->readonly()
                        ->withMeta([
                            'value' => ($sum = $contract->maturities->where('id', '<=', $this->id ?? $contract->maturities->max('id'))->sum('amount'))
                        ]),  

                    Number::make(__('Current installment'), 'installment')
                        ->required()
                        ->rules('required')
                        ->readonly()
                        ->onlyOnForms()
                        ->hideWhenUpdating()
                        ->withMeta([
                            'value' => $contract->maturities->count() + ($this->exists ? 0:1)
                        ]), 

                    Currency::make(__('Debt until here'), 'amount')
                        ->required()
                        ->rules('required')
                        ->readonly()
                        ->withMeta([
                            'value' => ($contract->installments * $contract->amount) - $sum
                        ]),  

                    Currency::make(__('Lacks'), 'amount')
                        ->required()
                        ->rules('required')
                        ->readonly()
                        ->withMeta([
                            'value' => $sum - ($contract->maturities->where('id', '<=', $this->id ?? $contract->maturities->max('id'))->count() * $contract->amount)
                        ]),  
                ];
            }),

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
                ->sortable(), 

            Medialibrary::make(__('Attachments'), 'attachments')
                ->autouploading()
                ->hideFromIndex()
                ->nullable(),
    	];
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