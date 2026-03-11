<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('users');
            $table->date('date');
            $table->boolean('available');
            $table->timestamps();
            $table->unique(['worker_id','date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_availabilities');
    }
};
