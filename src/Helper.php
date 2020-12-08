<?php

namespace Zareismail\Bonchaq;

use Carbon\{CarbonInterface, Carbon}; 
use Illuminate\Support\Str;
use Laravel\Nova\Nova;


class Helper
{    
    /**
     * The hourly place holder
     */
    const HOURLY = 'hourly';

    /**
     * The daily place holder
     */
    const DAILY = 'daily';

    /**
     * The weekly place holder
     */
    const WEEKLY = 'weekly';

    /**
     * The monthly place holder
     */
    const MONTHLY = 'monthly';

    /**
     * The yearly place holder
     */
    const YEARLY = 'yearly'; 
    
    /**
     * Get an array of periods date.
     * 
     * @return array
     */
    public static function periods()
    {
        return [ 
            static::HOURLY     => __(Str::title(static::HOURLY)),
            static::DAILY     => __(Str::title(static::DAILY)),
            static::WEEKLY    => __(Str::title(static::WEEKLY)),
            static::MONTHLY   => __(Str::title(static::MONTHLY)),
            static::YEARLY    => __(Str::title(static::YEARLY)),
        ];
    }  

    /**
     * Return Nova's contractable resources.
     * 
     * @return \Laravel\Nova\ResourceCollection
     */
    public static function contractables()
    {
        return Nova::authorizedResources(app('request'))->filter(function($resource) { 
            return collect(class_implements($resource::newModel()))->contains(Contracts\Contractable::class); 
        })->values();
    } 

    /**
     * Get the days of the week.
     *
     * @return array
     */
    public static function getDays()
    {
        return Carbon::getDays();
    } 

    /**
     * Get the months of the year.
     *
     * @return array
     */
    public static function getMonths()
    {
        return [ 
            CarbonInterface::JANUARY   => 'January', 
            CarbonInterface::FEBRUARY  => 'February', 
            CarbonInterface::MARCH     => 'March', 
            CarbonInterface::APRIL     => 'April', 
            CarbonInterface::MAY       => 'May', 
            CarbonInterface::JUNE      => 'June', 
            CarbonInterface::JULY      => 'July', 
            CarbonInterface::AUGUST    => 'August', 
            CarbonInterface::SEPTEMBER => 'September', 
            CarbonInterface::OCTOBER   => 'October', 
            CarbonInterface::NOVEMBER  => 'November', 
            CarbonInterface::DECEMBER  => 'December', 
        ];
    }

    /**
     * Returns array of periods with the equivalent days.
     * 
     * @return array
     */
    public static function periodEquivalentDays()
    {
        return [
            static::HOURLY  => 1/24,
            static::DAILY   => 1,
            static::WEEKLY  => 7,
            static::MONTHLY => 30,
            static::YEARLY  => 365,
        ];
    }

    /**
     * Returns equivalent days of the given period.
     * 
     * @return int
     */
    public static function periodEquivalentDay(string $period): int
    {
        return static::periodEquivalentDays()[$period] ?? 1;
    }
}
