<?php

namespace Zareismail\Bonchaq\Models;

use Illuminate\Database\Eloquent\SoftDeletes;   
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Zareismail\NovaContracts\Models\AuthorizableModel;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\HasMedia;

class Model extends AuthorizableModel implements HasMedia
{ 
    use HasFactory, SoftDeletes, HasMediaTrait;  

	public function registerMediaCollections(): void
	{ 
	    $this->addMediaCollection('attachments');
	}
}
