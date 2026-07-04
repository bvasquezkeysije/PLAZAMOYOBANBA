<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_registers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('full_name', 150);
            $table->string('document_type', 20);
            $table->string('document_number', 20);
            $table->string('nationality', 80)->default('PERUANA');
            $table->dateTime('check_in_at');
            $table->dateTime('check_out_at')->nullable();
            $table->string('status', 20)->default('hospedado');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['document_number']);
            $table->index(['status']);
            $table->index(['check_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_registers');
    }
};

