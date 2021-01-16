<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Zareismail\Bonchaq\Helper;

class ChangesPaymentDateColumnOnMaturitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->setConfig();

        Schema::table('bonchaq_maturities', function (Blueprint $table) { 
            $table->datetime('payment_date')->change(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonchaq_maturities', function (Blueprint $table) { 
            $table->timestamp('payment_date')->change(); 
        });
    }

    public function setConfig($value='')
    { 
        app('config')->set('database.dbal', [
            'types' => [
                'timestamp' => \Illuminate\Database\DBAL\TimestampType::class,
            ],
        ]);
    }
}
