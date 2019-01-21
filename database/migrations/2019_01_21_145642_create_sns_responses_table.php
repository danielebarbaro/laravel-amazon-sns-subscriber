<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSnsResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sns_responses', function (Blueprint $table) {
            $table->uuid('uuid')->unique();
            $table->primary('uuid');
            $table->string('email')->index();
            $table->string('type')->nullable();
            $table->string('source_email')->nullable();
            $table->string('source_arn')->nullable();
            $table->string('unsubscribe_url')->nullable();
            $table->json('data_payload')->nullable();
            $table->dateTime('datetime_payload')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sns_responses');
    }
}
