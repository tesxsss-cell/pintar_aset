<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{public function up(): void
    {Schema::create('asset_update_reports', function (Blueprint $table) {$table->id(); $table->foreignId('asset_id')->constrained()->cascadeOnDelete(); $table->string('reporter_name'); $table->string('reporter_email')->nullable(); $table->string('reporter_phone', 30)->nullable(); $table->text('reason'); $table->string('proposed_name')->nullable(); $table->string('proposed_owner')->nullable(); $table->string('proposed_location')->nullable(); $table->string('proposed_condition')->nullable(); $table->string('proposed_category')->nullable(); $table->text('proposed_description')->nullable(); $table->string('status')->default('pending')->index(); $table->text('admin_note')->nullable(); $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('reviewed_at')->nullable(); $table->timestamps();});}public function down(): void
    {Schema::dropIfExists('asset_update_reports');}};
