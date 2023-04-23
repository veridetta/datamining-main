<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_parameters', function (Blueprint $table) {
            $table->string('no_rangka', 18)->primary();
            $table->string('type_mobil', 3)->nullable();
            $table->string('pkb_type', 3)->nullable();
            $table->string('kilometer', 3)->nullable();
            $table->string('total_revenue', 3)->nullable();
            $table->string('tahun_kontruksi', 3)->nullable();
            $table->string('service_kategori', 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_parameters');
    }
}
