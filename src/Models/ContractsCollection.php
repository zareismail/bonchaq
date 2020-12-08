<?php

namespace Zareismail\Bonchaq\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ContractsCollection extends Collection
{  	
	/** 
	 * Returns the started contracrs.
	 * 
	 * @return $this
	 */
	public function started()
	{
		return $this->filter(function($contract) {
			return optional($contract->start_date)->lte(now());
		});
	}

	/** 
	 * Returns the none-expired contracrs.
	 * 
	 * @return $this
	 */
	public function inProgress()
	{
		return $this->started()->filter(function($contract) {
			return optional($contract->end_date)->gt(now());
		});
	}

	/**
	 * Returns the expired contracrs.
	 * 
	 * @return $this
	 */
	public function expired()
	{
		return $this->started()->filter(function($contract) {
			return optional($contract->end_date)->lt(now());
		});
	}

	/**
	 * Returns the none-started contracrs.
	 * 
	 * @return $this
	 */
	public function onHold()
	{
		return $this->filter(function($contract) {
			return is_null($contract->start_date) || $contract->start_date->gt(now());
		});
	}

	/**
	 * Filters the items with the given model.
	 *
	 * @param \Laravel\Nova\Resource $resource
	 * @return $this
	 */
	public function forResource($resource)
	{
		return $this->where('contractable_type', $resource::newModel()->getMorphClass());
	}
}
