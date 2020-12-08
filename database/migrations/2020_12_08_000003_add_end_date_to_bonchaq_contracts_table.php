<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Zareismail\Bonchaq\Helper;

class AddEndDateToBonchaqContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonchaq_contracts', function (Blueprint $table) { 
            $table->timestamp('end_date')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonchaq_contracts', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });
    }
}
