<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(\App\Models\LatestPriceCandle::TABLE_NAME, function (Blueprint $table) {
            $table->comment('Latest price data in USD at time');

            $table->id();
            $table->string('symbol', 16);
            $table->float('open', precision: 4)->unsigned();
            $table->float('high', precision: 4)->unsigned();
            $table->float('low', precision: 4)->unsigned();
            $table->float('price', precision: 4)->unsigned()->comment('Actual price at time');
            $table->integer('volume')->unsigned();
            $table->date('latest_trading_date');
            $table->float('prev_close', precision: 4);
            $table->float('change', precision: 4);
            $table->float('change_percent', precision: 4);
            $table->dateTime('time')->comment('UTC time');

            $table->index('id');
            $table->index('symbol');
            $table->index('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(\App\Models\LatestPriceCandle::TABLE_NAME);
    }
};
