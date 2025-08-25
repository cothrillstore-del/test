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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['user', 'admin', 'editor', 'reviewer'])->default('user')->after('email');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            if (!Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'banned_at')) {
                $table->timestamp('banned_at')->nullable()->after('is_banned');
            }
            if (!Schema::hasColumn('users', 'banned_reason')) {
                $table->text('banned_reason')->nullable()->after('banned_at');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (!Schema::hasColumn('users', 'location')) {
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('users', 'handicap')) {
                $table->decimal('handicap', 3, 1)->nullable();
            }
            if (!Schema::hasColumn('users', 'skill_level')) {
                $table->enum('skill_level', ['beginner', 'intermediate', 'advanced', 'pro'])->nullable();
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip')->nullable();
            }
            
            // Indexes
            $table->index('role');
            $table->index('is_active');
            $table->index('is_banned');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'is_active', 'is_banned', 'banned_at', 'banned_reason',
                'phone', 'avatar', 'bio', 'location', 'handicap', 'skill_level',
                'last_login_at', 'last_login_ip'
            ]);
        });
    }
};
