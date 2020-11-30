<?php

namespace Zareismail\Bonchaq\Models;


class BonchaqSubject extends Model
{
	/**
	 * Query the related BonchaqContract`s.
	 * 
	 * @return  \Illuminate\Database\Eloquent\Relations\HasMany     
	 */
	public function contracts()
	{ 
		return $this->hasMany(BonchaqContract::class, 'subject_id');
	}
}
