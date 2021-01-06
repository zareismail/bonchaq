<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Zareismail\Bonchaq\Helper;

class AddInstallmentToBonchaqMaturitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonchaq_maturities', function (Blueprint $table) { 
            $table->integer('installment')->nullable(); 
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
            $table->dropColumn('installment');
        });
    }
}
