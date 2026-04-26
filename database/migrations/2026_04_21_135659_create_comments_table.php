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
        Schema::create('comments', function (Blueprint $table) {
    $table->id('id_comment');
    $table->unsignedBigInteger('id_post');
    $table->string('nama_pengunjung', 100);
    $table->string('email', 100);
    $table->text('isi_komentar');
    $table->date('tanggal');

    $table->foreign('id_post')->references('id_post')->on('posts');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
