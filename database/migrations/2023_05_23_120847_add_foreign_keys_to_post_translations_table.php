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
        Schema::table('post_translations', function (Blueprint $table) {
            $table->foreign(['language_id'], 'post_translations_language_id_fk')->references(['id'])->on('languages')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign(['post_id'], 'post_translations_post_id_fk')->references(['id'])->on('posts')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_translations', function (Blueprint $table) {
            $table->dropForeign('post_translations_language_id_fk');
            $table->dropForeign('post_translations_post_id_fk');
        });
    }
};
