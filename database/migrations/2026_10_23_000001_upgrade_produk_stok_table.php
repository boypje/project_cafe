<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Produk mendapat data HPP, batas stok minimum, dan resep (komponen),
     * sehingga menjual "Paket Nasi + Bebek" ikut mengurangi stok Nasi & Bebek.
     */
    public function up()
    {
        Schema::table('produk', function (Blueprint $table) {
            if (! Schema::hasColumn('produk', 'harga_pokok')) {
                $table->integer('harga_pokok')->default(0)->after('harga_jual');
            }
            if (! Schema::hasColumn('produk', 'satuan')) {
                $table->string('satuan', 20)->default('porsi')->after('stok');
            }
            if (! Schema::hasColumn('produk', 'stok_minimum')) {
                $table->integer('stok_minimum')->default(5)->after('satuan');
            }
            if (! Schema::hasColumn('produk', 'kelola_stok')) {
                $table->boolean('kelola_stok')->default(true)->after('stok_minimum');
            }
            if (! Schema::hasColumn('produk', 'tampil_kasir')) {
                $table->boolean('tampil_kasir')->default(true)->after('kelola_stok');
            }
        });

        if (! Schema::hasTable('produk_komponen')) {
            Schema::create('produk_komponen', function (Blueprint $table) {
                $table->increments('id_produk_komponen');
                $table->unsignedInteger('id_produk');
                $table->unsignedInteger('id_komponen');
                $table->integer('jumlah')->default(1);
                $table->timestamps();

                $table->unique(['id_produk', 'id_komponen']);
                $table->foreign('id_produk')->references('id_produk')->on('produk')->cascadeOnDelete();
                $table->foreign('id_komponen')->references('id_produk')->on('produk')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('stok_mutasi')) {
            Schema::create('stok_mutasi', function (Blueprint $table) {
                $table->increments('id_stok_mutasi');
                $table->unsignedInteger('id_produk');
                // penjualan | batal | masuk | keluar | koreksi
                $table->string('tipe', 20);
                $table->integer('jumlah');
                $table->integer('stok_akhir');
                $table->integer('harga_pokok')->default(0);
                $table->string('keterangan')->nullable();
                $table->unsignedInteger('id_penjualan')->nullable();
                $table->unsignedBigInteger('id_user')->nullable();
                $table->timestamps();

                $table->index(['id_produk', 'created_at']);
                $table->index(['tipe', 'created_at']);
                $table->index('id_penjualan');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('stok_mutasi');
        Schema::dropIfExists('produk_komponen');

        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['harga_pokok', 'satuan', 'stok_minimum', 'kelola_stok', 'tampil_kasir']);
        });
    }
};
