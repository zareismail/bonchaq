<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Zareismail\Bonchaq\Helper;

class CreateBonchaqContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonchaq_contracts', function (Blueprint $table) {
            $table->id(); 
            $table->auth(); 
            $table->morphs('contractable'); 
            $table->foreignId('subject_id')->constrained('bonchaq_subjects'); 
            $table->enum('period', array_keys(Helper::periods()))->default(Helper::MONTHLY);
            $table->tinyInteger('maturity')->default(1);  
            $table->tinyInteger('installments')->default(1);  
            $table->timestamp('start_date')->nullable();
            $table->price('amount');
            $table->price('advance_payment')->default(0);
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
        Schema::dropIfExists('bonchaq_contracts');
    }
}
