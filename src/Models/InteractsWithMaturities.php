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
        collect(range(1, $this->installments))->each(function($installment) {
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
            'installment'   => $this->installment,
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
        return $this->start_date->addDays(
            Helper::periodEquivalentDay($this->period) * $installment
        );
    }
}
