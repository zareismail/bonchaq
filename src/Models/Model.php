<?php

namespace Zareismail\Bonchaq\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model as LaravelModel, SoftDeletes};   
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Model extends LaravelModel implements HasMedia
{ 
    use HasFactory, SoftDeletes, HasMediaTrait;  

	public function registerMediaCollections(): void
	{ 
	    $this->addMediaCollection('attachments');
	}
}
