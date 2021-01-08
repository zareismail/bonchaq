<?php

namespace Zareismail\Bonchaq\Models;

use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Zareismail\NovaContracts\Models\AuthorizableModel;
use Zareismail\Contracts\Concerns\InteractsWithDetails;

class BonchaqMaturity extends AuthorizableModel implements HasMedia
{ 
	use HasMediaTrait, InteractsWithDetails;
	
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

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new MaturityCollection($models);
    }
}
