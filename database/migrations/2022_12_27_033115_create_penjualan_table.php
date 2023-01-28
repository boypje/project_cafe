<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id('id_penjualan');
            $table->integer('total_item');
            $table->integer('total_harga');
            $table->integer('bayar')->default(0);
            $table->integer('diterima')->default(0);
            $table->unsignedBigInteger('id_user')->nullable();
            $table->foreign('id_user')->references("id")->on("users")->onDelete("null");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penjualan');
    }
};
