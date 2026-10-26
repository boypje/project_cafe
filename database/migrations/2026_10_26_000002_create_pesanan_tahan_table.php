<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pesanan yang sudah dicatat tapi belum dibayar (open bill), mis. pelanggan
     * makan dulu bayar belakangan. Stok baru dipotong saat pesanan dibayar.
     */
    public function up()
    {
        Schema::create('pesanan_tahan', function (Blueprint $table) {
            $table->increments('id_pesanan_tahan');
            $table->string('meja', 20)->nullable();
            $table->string('nama_pelanggan', 100)->nullable();
            $table->unsignedSmallInteger('pengunjung')->default(1);
            $table->string('catatan')->nullable();
            $table->json('items');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pesanan_tahan');
    }
};
