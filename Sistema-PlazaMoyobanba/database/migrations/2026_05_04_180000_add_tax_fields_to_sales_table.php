<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('document_type', 20)->default('boleta')->after('code');
            $table->string('series', 10)->nullable()->after('document_type');
            $table->unsignedBigInteger('correlative')->nullable()->after('series');
            $table->decimal('subtotal', 10, 2)->default(0)->after('total');
            $table->decimal('igv', 10, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'document_type',
                'series',
                'correlative',
                'subtotal',
                'igv',
            ]);
        });
    }
};

