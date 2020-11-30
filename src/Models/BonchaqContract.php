<?php

namespace Zareismail\Bonchaq\Models;

use Zareismail\NovaContracts\Models\AuthorizableModel;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class BonchaqContract extends AuthorizableModel implements HasMedia
{ 
	use HasMediaTrait;
	
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
    	'start_date' => 'datetime',
    ];

	/**
	 * Query the related BonchaqSubject.
	 * 
	 * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo     
	 */
	public function subject()
	{ 
		return $this->belongsTo(BonchaqSubject::class);
	}

	/**
	 * Query the related contractable.
	 * 
	 * @return  \Illuminate\Database\Eloquent\Relations\MorphTo     
	 */
	public function contractable()
	{ 
		return $this->morphTo();
	} 

	/**
	 * Query the related Maturity.
	 * 
	 * @return  \Illuminate\Database\Eloquent\Relations\HasMany     
	 */
	public function maturities()
	{ 
		return $this->hasMany(BonchaqMaturity::class, 'contract_id');
	} 

	public function registerMediaCollections(): void
	{ 
	    $this->addMediaCollection('attachments');
	}
}
