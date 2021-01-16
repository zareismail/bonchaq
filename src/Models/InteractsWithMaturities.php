<?php

namespace Zareismail\Bonchaq\Models;

use Zareismail\Bonchaq\Helper;

trait InteractsWithMaturities  
{   
    /**
     * Query the related Maturity.
     * 
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany     
     */
    public function maturities()
    { 
        return $this->hasMany(BonchaqMaturity::class, 'contract_id');
    } 

    /**
     * Create related maturites.
     * 
     * @return $this
     */
    public function creatInstallments()
    {
        collect()->range(0, $this->installments)->each(function($installment) {
            $this->fillMaturity($installment)->save();
        });
    }

    /**
     * Create new Maturiy instance.
     * 
     * @param  integer $installment 
     * @return \Zareismail\Bonchaq\Models\BonchaqMaturity              
     */
    public function fillMaturity(int $installment)
    {
        return (new BonchaqMaturity)->forceFill([
            'payment_date'  => $this->calculateInstallmentsDate($installment),
            'installment'   => $installment,
            'contract_id'   => $this->getKey(),
            'auth_id'       => $this->auth_id,
            'details'       => [],
        ]);
    }

    /**
     * Returns Installments datetime for the given installment.
     * 
     * @param  integer $installment 
     * @return \Datetime              
     */
    public function calculateInstallmentsDate(int $installment)
    {
        switch ($this->period) {
            case 'yearly':
                return $this->start_date->addYears($installment)->setUnit(
                    'month', $this->maturity
                );
                break;

            case 'monthly':
                return $this->start_date->addMonths($installment)->setUnit(
                    'day', $this->maturity
                );
                break;

            case 'weekly':
                return $this->start_date->addWeeks($installment)->setUnit(
                    'day', $this->maturity
                );
                break;

            case 'daily':
                return $this->start_date->addDays($installment)->setUnit(
                    'hour', $this->maturity
                );
                break;

            case 'hourly':
                return $this->start_date->addHours($installment);
                break; 
        } 
    }
}
