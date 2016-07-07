<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPointColumnToParticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->integer('points')->after('name')->default(0);
            $table->integer('games_played')->after('points')->default(0);
            $table->integer('diff')->after('points')->default(0);
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('instagram_tag');
            $table->integer('playoff_size')->after('slug')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('points');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('instagram_tag')->after('slug');
            $table->dropColumn('playoff_size');
            $table->dropColumn('games_played');
            $table->dropColumn('diff');
        });
    }
}
