<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFinishedFieldToMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->integer('home_team_from')->nullable()->unsigned()->after('tournament_id');
            $table->integer('away_team_from')->nullable()->unsigned()->after('tournament_id');
            $table->boolean('round')->default(1)->after('tournament_id');
            $table->boolean('finished')->default(0)->after('away_score');
            $table->boolean('playoff')->default(0)->after('finished');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('finished');
            $table->dropColumn('round');
            $table->dropColumn('home_team_from');
            $table->dropColumn('away_team_from');
            $table->dropColumn('playoff');
        });
    }
}
