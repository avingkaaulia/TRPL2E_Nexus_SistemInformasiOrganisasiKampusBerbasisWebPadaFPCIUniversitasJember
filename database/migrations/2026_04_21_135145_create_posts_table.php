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
        Schema::create('posts', function (Blueprint $table) {
    $table->id('id_post');
    $table->string('title', 150);
    $table->text('content');
    $table->unsignedBigInteger('id_post_category');
    $table->enum('post_type', ['post','page']);
    $table->unsignedBigInteger('id_user');
    $table->date('date_published');
    $table->enum('status', ['draft','publish','pending']);
    $table->string('featured_image_path', 255);

    $table->foreign('id_post_category')->references('id_category')->on('post_category');
    $table->foreign('id_user')->references('id_user')->on('users');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
