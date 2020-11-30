<?php

namespace Zareismail\Bonchaq\Models;

use Zareismail\NovaContracts\Models\AuthorizableModel;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class BonchaqMaturity extends AuthorizableModel implements HasMedia
{ 
	use HasMediaTrait;
	
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
    	'payment_date' => 'datetime',
    ];

	/**
	 * Query the related BonchaqContract.
	 * 
	 * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo     
	 */
	public function contract()
	{ 
		return $this->belongsTo(BonchaqContract::class);
	} 

	public function registerMediaCollections(): void
	{ 
	    $this->addMediaCollection('attachments');
	}
}
