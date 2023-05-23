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
        Schema::table('post_tags', function (Blueprint $table) {
            $table->foreign(['post_id'], 'post_tags_post_id_fk')->references(['id'])->on('posts')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign(['tag_id'], 'post_tags_tag_id_fk')->references(['id'])->on('tags')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_tags', function (Blueprint $table) {
            $table->dropForeign('post_tags_post_id_fk');
            $table->dropForeign('post_tags_tag_id_fk');
        });
    }
};
