<?php

namespace Zareismail\Bonchaq\Models;

use Zareismail\NovaContracts\Models\AuthorizableModel;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Zareismail\Bonchaq\Helper;

class BonchaqContract extends AuthorizableModel implements HasMedia
{ 
	use HasMediaTrait, InteractsWithMaturities;
	
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
    	'start_date' => 'datetime',
    	'end_date' => 'datetime',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
    	parent::boot();

    	static::saving(function($model) {
    		$model->fillEndDate();
    	}); 

        static::created(function($model) {
            $model->creatInstallments();
        }); 
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new ContractsCollection($models);
    }

    /**
     * Fill the end_date attribute.
     *
     * @return $this
     */
    public function fillEndDate()
    {
    	return $this->forceFill([
    		'end_date' => $this->calculateEndDate()
    	]);  
    } 

    /**
     * Calculate the end_time of the contracts.
     * 
     * @return \Carbon\Carbon
     */
    public function calculateEndDate()
    {
        if(is_null($this->start_date)) {
            return null;
        } else if($this->period === Helper::HOURLY) {
    		return now()->addHours($this->installments);
    	}

    	return $this->start_date->addDays(
            $this->installments * Helper::periodEquivalentDay($this->period)
        );
    } 

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
	 * Query the started contracts.
	 * 
	 * @param \Illuminate\Database\Eloquent\Builder  $query
	 * @return \Illuminate\Database\Eloquent\Builder       
	 */
	public function scopeStarted($query)
	{ 
		return $query->whereDate($query->qualifyColumn('start_date'), '<=', now()); 
	} 

	/**
	 * Query the in-progress contracts.
	 * 
	 * @param \Illuminate\Database\Eloquent\Builder  $query
	 * @return \Illuminate\Database\Eloquent\Builder       
	 */
	public function scopeInProgress($query)
	{
		return $query->started()->whereDate($query->qualifyColumn('end_date'), '>=', now()); 
	}

    public function registerMediaCollections(): void
    { 
        $this->addMediaCollection('attachments');
    }
}
