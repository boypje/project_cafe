<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom tambahan untuk kasir baru (meja, pelanggan, pajak, HPP per item),
     * kategori pengeluaran, pengaturan pajak & nota, serta index untuk laporan.
     */
    public function up()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            if (! Schema::hasColumn('penjualan', 'nama_pelanggan')) {
                $table->string('nama_pelanggan', 100)->nullable()->after('pengunjung');
            }
            if (! Schema::hasColumn('penjualan', 'meja')) {
                $table->string('meja', 20)->nullable()->after('nama_pelanggan');
            }
            if (! Schema::hasColumn('penjualan', 'pajak')) {
                $table->integer('pajak')->default(0)->after('diskon');
            }
            if (! Schema::hasColumn('penjualan', 'catatan')) {
                $table->string('catatan')->nullable()->after('diterima');
            }
            $table->index('created_at', 'penjualan_created_at_index');
            $table->index('id_user', 'penjualan_id_user_index');
        });

        Schema::table('penjualandetail', function (Blueprint $table) {
            if (! Schema::hasColumn('penjualandetail', 'harga_pokok')) {
                $table->integer('harga_pokok')->default(0)->after('harga_jual');
            }
            $table->index('id_penjualan', 'penjualandetail_id_penjualan_index');
            $table->index('id_produk', 'penjualandetail_id_produk_index');
        });

        Schema::table('pengeluaran', function (Blueprint $table) {
            if (! Schema::hasColumn('pengeluaran', 'kategori')) {
                $table->string('kategori', 50)->default('Operasional')->after('deskripsi');
            }
            if (! Schema::hasColumn('pengeluaran', 'id_user')) {
                $table->unsignedBigInteger('id_user')->nullable()->after('metode');
            }
            $table->index('created_at', 'pengeluaran_created_at_index');
        });

        Schema::table('setting', function (Blueprint $table) {
            if (! Schema::hasColumn('setting', 'pajak_persen')) {
                $table->decimal('pajak_persen', 5, 2)->default(0)->after('path_logo');
            }
            if (! Schema::hasColumn('setting', 'footer_nota')) {
                $table->string('footer_nota')->nullable()->after('pajak_persen');
            }
            if (! Schema::hasColumn('setting', 'blokir_stok_habis')) {
                $table->boolean('blokir_stok_habis')->default(false)->after('footer_nota');
            }
        });
    }

    public function down()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropIndex('penjualan_created_at_index');
            $table->dropIndex('penjualan_id_user_index');
            $table->dropColumn(['nama_pelanggan', 'meja', 'pajak', 'catatan']);
        });
        Schema::table('penjualandetail', function (Blueprint $table) {
            $table->dropIndex('penjualandetail_id_penjualan_index');
            $table->dropIndex('penjualandetail_id_produk_index');
            $table->dropColumn('harga_pokok');
        });
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->dropIndex('pengeluaran_created_at_index');
            $table->dropColumn(['kategori', 'id_user']);
        });
        Schema::table('setting', function (Blueprint $table) {
            $table->dropColumn(['pajak_persen', 'footer_nota', 'blokir_stok_habis']);
        });
    }
};
