<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('full_name', 160);
            $table->string('document_number', 11)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable()->unique();
            $table->string('address', 200)->nullable();
            $table->foreignId('role_id')->constrained('roles');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};

