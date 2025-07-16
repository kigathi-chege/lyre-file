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
        if (!Schema::hasTable('files')) {
            Schema::create('files', function (Blueprint $table) {
                basic_fields($table, 'files');
                $table->string('name')->unique();
                $table->text('path')->nullable();
                $table->text('path_sm')->nullable();
                $table->text('path_md')->nullable();
                $table->text('path_lg')->nullable();
                $table->integer('size')->default(0);
                $table->string('extension')->nullable();
                $table->string('mimetype')->nullable();
                $table->integer('usagecount')->default(1);
                $table->string('checksum')->nullable();
                $table->dateTime('viewed_at')->nullable();
                $table->string('storage')->default("local");
            });
        }

        if (!Schema::hasColumn('files', 'original_name')) {
            Schema::table('files', function (Blueprint $table) {
                $table->string('original_name')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
