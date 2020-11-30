<?php

namespace Zareismail\Bonchaq\Concerns; 

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Zareismail\Bonchaq\Models\BonchaqContract;

trait InteractsWithContracts
{ 
	/**
	 * Query the related contracts.
	 * 
	 * @return \Illuminate\Database\Eloquent\Relations\HasOneOrMany
	 */
	public function contracts(): HasOneOrMany
	{
		return $this->morphMany(BonchaqContract::class, 'contractable');
	}
} 