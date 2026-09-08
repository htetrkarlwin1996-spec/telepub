<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('royalty_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_hash', 64)->unique();
            $table->unsignedInteger('row_count')->default(0);
            $table->foreignId('entered_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('royalties', function (Blueprint $table) {
            $table->foreignId('royalty_import_id')->nullable()->after('entered_by')->constrained()->nullOnDelete();
            $table->unsignedInteger('import_row')->nullable()->after('royalty_import_id');
        });
    }

    public function down(): void
    {
        Schema::table('royalties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('royalty_import_id');
            $table->dropColumn('import_row');
        });
        Schema::dropIfExists('royalty_imports');
    }
};
