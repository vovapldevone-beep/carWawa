<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
                // ID клієнта
            $table->integer('client_id');
            $table->integer('tow_id');

            // Дані авто
            $table->string('car_name');
            $table->string('car_number');
            $table->decimal('car_weight', 8, 2)->nullable();

            // Локація авто
            $table->string('car_location');

            // Статус замовлення
            $table->string('status')->default('pending');

            // Дата завантаження
            $table->dateTime('loading_date')->nullable();

            // Відстань
            $table->integer('distance')->nullable();
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
    
};
