<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('short_description');
            $table->longText('description')->nullable();
            $table->string('category')->default('Module'); // Module|Plan|Add-on
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_yearly', 12, 2)->default(0);
            $table->string('currency', 8)->default('BDT');
            $table->string('icon')->default('bi-box');
            $table->string('badge')->nullable(); // Popular, New, Enterprise
            $table->json('features')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('New'); // New|Contacted|Closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_inquiries');
        Schema::dropIfExists('products');
    }
};
