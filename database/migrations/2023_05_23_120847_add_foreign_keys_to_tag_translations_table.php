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
        Schema::table('tag_translations', function (Blueprint $table) {
            $table->foreign(['language_id'], 'tags_translations_language_id_fk')->references(['id'])->on('languages')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign(['tag_id'], 'tags_translations_tag_id_fk')->references(['id'])->on('tags')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tag_translations', function (Blueprint $table) {
            $table->dropForeign('tags_translations_language_id_fk');
            $table->dropForeign('tags_translations_tag_id_fk');
        });
    }
};
