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
                ->rules('required'), 

            DateTime::make(__('Payment Date'), 'payment_date')
                ->required()
                ->rules('required'), 

            Medialibrary::make(__('Attachments'), 'attachments')
                ->autouploading()
                ->hideFromIndex()
                ->nullable(),
    	];
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
}