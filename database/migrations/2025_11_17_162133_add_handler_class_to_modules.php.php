<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class  extends Migration
{
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('handler_class')->nullable()->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('handler_class');
        });
    }
};
