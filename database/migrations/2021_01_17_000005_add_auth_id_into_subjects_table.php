<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema; 
use Zareismail\NovaContracts\Models\User;

class AddAuthIdIntoSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::disableForeignKeyConstraints();

        $developer = User::get()->filter->isDeveloper()->first();

        Schema::hasColumn('bonchaq_subjects', 'auth_id') || 
        Schema::table('bonchaq_subjects', function (Blueprint $table) use ($developer) { 
            $table->auth()->default($developer->id); 
        });
        
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::hasColumn('bonchaq_subjects', 'auth_id') && 
        Schema::table('bonchaq_subjects', function (Blueprint $table) { 
            $table->dropAuth(); 
        });
    } 
}
