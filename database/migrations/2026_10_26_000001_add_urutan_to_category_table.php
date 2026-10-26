<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Urutan tampil kategori di kasir, mengikuti urutan di buku menu. */
    public function up()
    {
        if (! Schema::hasColumn('category', 'urutan')) {
            Schema::table('category', function (Blueprint $table) {
                $table->unsignedSmallInteger('urutan')->default(99)->after('nama_kategori');
            });
        }
    }

    public function down()
    {
        Schema::table('category', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
