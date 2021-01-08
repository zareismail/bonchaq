<?php

namespace Zareismail\Bonchaq\Models;

use Illuminate\Database\Eloquent\Collection;

class MaturityCollection extends Collection
{    
	/**
	 * Returns maturities between the given installments.
	 * 
	 * @param  int|null $from 
	 * @param  int|null $to   
	 * @return $this       
	 */
	public function between(int $from = null, int $to = null)
	{
		return $this->where('installment', '>=', $from)
					->where('installment', '<=', $to ?: $this->max('installment'));
	}

	/**
	 * Returns total payment.
	 * 
	 * @return float       
	 */
	public function totalPayment()
	{
		return $this->sum('amount');
	}
}
