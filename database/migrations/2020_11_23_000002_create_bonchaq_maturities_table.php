<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Zareismail\Bonchaq\Helper;

class CreateBonchaqMaturitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonchaq_maturities', function (Blueprint $table) {
            $table->id(); 
            $table->auth();  
            $table->foreignId('contract_id')->constrained('bonchaq_contracts');    
            $table->string('tracking_code')->nullable();
            $table->timestamp('payment_date');
            $table->price('amount'); 
            $table->details(); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonchaq_maturities');
    }
}
