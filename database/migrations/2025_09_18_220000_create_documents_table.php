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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // statement, receipt, contract, invoice, report, etc.
            $table->string('category'); // investment, client, system, etc.
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type'); // pdf, doc, xls, etc.
            $table->bigInteger('file_size'); // in bytes
            $table->string('mime_type');
            $table->string('hash'); // file hash for integrity
            $table->string('status')->default('active'); // active, archived, deleted
            $table->string('visibility')->default('private'); // private, public, restricted
            $table->json('metadata')->nullable(); // Additional document metadata
            $table->json('tags')->nullable(); // Document tags for categorization
            $table->string('reference_type')->nullable(); // order, investment, inquiry, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'category']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['status', 'visibility']);
            $table->index('uploaded_by');
            $table->index('approved_by');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
