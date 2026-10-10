<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cada versión semestral de la Cátedra (ej. "Cátedra UNINAVARRA 2026-2")
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('period')->nullable();
            $table->string('event_name')->nullable();     // LION HUB EVENT
            $table->string('tagline')->nullable();        // Disruptive Minds
            $table->text('description')->nullable();
            $table->string('banner')->nullable();
            $table->dateTime('voting_starts_at')->nullable();
            $table->dateTime('voting_ends_at')->nullable();
            $table->dateTime('final_at')->nullable();
            $table->string('final_location')->nullable();
            $table->unsignedTinyInteger('community_weight')->default(30); // % que pesa la votación
            $table->unsignedTinyInteger('max_votes_per_user')->default(3);
            $table->unsignedTinyInteger('finalists_count')->default(5);
            $table->string('voting_email_domain')->nullable(); // ej. uninavarra.edu.co
            $table->boolean('is_active')->default(false)->index();
            $table->boolean('is_published')->default(true);
            $table->boolean('results_published')->default(false);
            $table->timestamps();
        });

        // Cronograma / fases de cada edición
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 20)->default('#f5b70a');
            $table->string('icon', 16)->nullable(); // emoji
            $table->timestamps();
        });

        Schema::create('ventures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->text('problem')->nullable();
            $table->text('solution')->nullable();
            $table->text('target_market')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
            $table->string('video_url')->nullable();
            $table->string('academic_program')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('website')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('tiktok')->nullable();
            $table->decimal('funding_goal', 14, 2)->nullable();
            $table->decimal('committee_score', 5, 2)->nullable(); // 0-100, comité técnico
            $table->boolean('is_finalist')->default(false);
            $table->unsignedTinyInteger('final_position')->nullable(); // 1 = ganador
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venture_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('academic_program')->nullable();
            $table->string('photo')->nullable();
            $table->string('linkedin')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('venture_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venture_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venture_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'venture_id']);
            $table->index(['edition_id', 'user_id']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venture_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5
            $table->boolean('is_approved')->default(true)->index();
            $table->timestamps();
        });

        // Intenciones de apoyo: no se maneja dinero, el equipo/admin hace el contacto
        Schema::create('support_pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venture_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);   // inversion | donacion | mentoria | alianza | compra
            $table->decimal('amount', 14, 2)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pendiente')->index(); // pendiente | contactado | en_proceso | concretado | descartado
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['support_pledges', 'comments', 'votes', 'venture_images', 'team_members', 'ventures', 'categories', 'milestones', 'editions'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
