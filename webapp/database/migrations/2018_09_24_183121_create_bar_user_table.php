<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBarUserTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('bar_user', function(Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('bar_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('role')->unsigned()->default(0);
            $table->timestamp('visited_at')->nullable(true);
            $table->timestamps();

            $table->foreign('bar_id')
                ->references('id')
                ->on('bars')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unique(['bar_id', 'user_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('bar_user');
    }
}
