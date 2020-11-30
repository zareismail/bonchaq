<?php

namespace Zareismail\Bonchaq\Contracts;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany; 

interface Contractable
{
	/**
	 * Query the related details.
	 * 
	 * @return \Illuminate\Database\Eloquent\Relations\HasOneOrMany
	 */
	public function contracts(): HasOneOrMany; 
} 