<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Catálogos base (sin dependencias)
        Schema::create('cat_user_roles', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('name', 50);
            $table->string('description', 150)->nullable();
            $table->boolean('whmcs_login')->nullable();
        });

        Schema::create('cat_permission_types', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('permission', 50)->default('');
        });

        Schema::create('tenancy_mode_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('description', 150)->nullable();
            $table->enum('tenancy_mode', ['shared', 'dedicated'])->default('shared');
        });

        // 2. Tablas principales
        Schema::create('organization', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('whmcs_id')->nullable();
            $table->string('name', 50);
            $table->string('description', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
        });

        Schema::create('company', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organization')->onDelete('cascade');
            $table->unsignedInteger('whmcs_id')->nullable();
            $table->string('name', 50);
            $table->string('description', 150)->nullable();
            $table->timestamps();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organization')->onDelete('cascade');
            $table->unsignedInteger('user_role_id')->nullable();
            $table->foreign('user_role_id')->references('id')->on('cat_user_roles');
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique()->nullable();
            $table->string('password', 255);
            $table->string('first_name', 60)->nullable();
            $table->string('last_name', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->integer('whmcs_id')->nullable();
            $table->integer('phone_extension')->nullable();
            $table->boolean('is_2fa_enabled')->default(false);
            $table->string('topt_secret', 32)->nullable();
            $table->integer('user_tag_id')->nullable();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description', 150)->nullable();
            $table->timestamps();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
        });

        // 3. Tablas relacionadas
        Schema::create('modules_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable()->constrained('modules');
            $table->unsignedInteger('required_permission_id')->nullable();
            $table->foreign('required_permission_id')->references('id')->on('cat_permission_types');
            $table->string('prefix', 50)->nullable();
            $table->string('route', 100);
            $table->enum('http_method', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH']);
            $table->boolean('is_active')->default(true);
            $table->string('description', 150)->nullable();
            $table->foreignId('tenancy_mode_group_id')->nullable()->constrained('tenancy_mode_groups');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->unique(['route', 'http_method']);
        });

        Schema::create('modules_endpoints_required_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_endpoint_id')->constrained('modules_endpoints')->onDelete('cascade');
            $table->unsignedInteger('user_role_id');
            $table->foreign('user_role_id')->references('id')->on('cat_user_roles');
            
            $table->unique(['module_endpoint_id', 'user_role_id'], 'me_req_roles_unique');
        });

        Schema::create('company_user_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('company')->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['company_id', 'name']);
        });

        Schema::create('company_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('company')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules');
            $table->boolean('is_active')->default(false);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->unique(['company_id', 'module_id']);
        });

        Schema::create('user_tags', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_user_tag_id')->constrained('company_user_tags')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('assigned_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->primary(['user_id', 'company_user_tag_id']);
        });

        Schema::create('users_modules_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules');
            $table->unsignedInteger('permission_id')->nullable();
            $table->foreign('permission_id')->references('id')->on('cat_permission_types');
            $table->timestamps();
        });

        Schema::create('users_company_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('company')->onDelete('cascade');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            
            $table->unique(['company_id', 'user_id']);
        });

        // 4. Tablas de tenancy y sesiones
        Schema::create('db_shared_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_mode_group_id')->nullable()->constrained('tenancy_mode_groups');
            $table->enum('env', ['development', 'production']);
            $table->string('db_host', 250);
            $table->string('db_name', 250);
            $table->string('db_user', 250);
            $table->string('db_pass', 250);
            $table->smallInteger('db_port')->default(3306);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('db_dedicated_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_mode_group_id')->constrained('tenancy_mode_groups');
            $table->foreignId('company_id')->constrained('company');
            $table->string('db_host', 512);
            $table->string('db_name', 512);
            $table->string('db_user', 512);
            $table->string('db_pass', 512);
            $table->smallInteger('db_port')->default(3306);
            $table->boolean('is_active')->default(true);
            $table->string('dek_encrypted', 512)->nullable();
        });

        Schema::create('session_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token_name', 80)->nullable();
            $table->string('token', 512);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('expires_at')->nullable();
            $table->string('created_by_ip', 20)->nullable();
            $table->enum('type', ['session', 'api', '2fa'])->nullable();
            $table->boolean('locked')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_tokens');
        Schema::dropIfExists('db_dedicated_connections');
        Schema::dropIfExists('db_shared_connections');
        Schema::dropIfExists('users_company_access');
        Schema::dropIfExists('users_modules_permissions');
        Schema::dropIfExists('user_tags');
        Schema::dropIfExists('company_modules');
        Schema::dropIfExists('company_user_tags');
        Schema::dropIfExists('modules_endpoints_required_roles');
        Schema::dropIfExists('modules_endpoints');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('users');
        Schema::dropIfExists('company');
        Schema::dropIfExists('organization');
        Schema::dropIfExists('tenancy_mode_groups');
        Schema::dropIfExists('cat_permission_types');
        Schema::dropIfExists('cat_user_roles');
    }
};
