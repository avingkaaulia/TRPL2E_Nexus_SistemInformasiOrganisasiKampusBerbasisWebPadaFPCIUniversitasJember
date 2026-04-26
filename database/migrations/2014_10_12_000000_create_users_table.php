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
        Schema::create('users', function (Blueprint $table) {
    $table->id('id_user');
    $table->string('username', 50);
    $table->string('email', 100);
    $table->string('password', 255);
    $table->string('nama', 100);
    $table->unsignedBigInteger('id_role');
    $table->date('tanggal_daftar');

    $table->foreign('id_role')->references('id_role')->on('roles');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
