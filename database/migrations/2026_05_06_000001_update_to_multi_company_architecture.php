<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        // 1. Create company_user pivot table
        if (!Schema::hasTable('company_user')) {
            Schema::create('company_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['company_id', 'user_id']);
            });
        }

        // 2. Migrate existing users.company_id data to company_user
        if (Schema::hasColumn('users', 'company_id')) {
            $users = DB::table('users')->whereNotNull('company_id')->get();
            foreach ($users as $user) {
                DB::table('company_user')->updateOrInsert(
                    ['company_id' => $user->company_id, 'user_id' => $user->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // 3. Add team_id to permission tables
        if ($teams) {
            $teamKey = $columnNames['team_foreign_key'] ?? 'team_id';

            // roles table
            if (!Schema::hasColumn($tableNames['roles'], $teamKey)) {
                Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
                    $table->unsignedBigInteger($teamKey)->nullable()->after('id');
                    $table->index($teamKey);
                });
            }
            
            try {
                Schema::table($tableNames['roles'], function (Blueprint $table) {
                    $table->dropUnique(['name', 'guard_name']);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
                    $table->unique([$teamKey, 'name', 'guard_name']);
                });
            } catch (\Exception $e) {}

            // model_has_permissions table
            if (!Schema::hasColumn($tableNames['model_has_permissions'], $teamKey)) {
                Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey, $pivotPermission) {
                    $table->unsignedBigInteger($teamKey)->after($pivotPermission);
                    $table->index($teamKey);
                });
            }
            
            try {
                Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($pivotPermission) {
                    $table->dropForeign([$pivotPermission]);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
                    $table->dropPrimary('model_has_permissions_permission_model_type_primary');
                });
            } catch (\Exception $e) {}
            
            try {
                Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey, $pivotPermission) {
                    $table->primary([$teamKey, $pivotPermission, 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
                });
            } catch (\Exception $e) {}
            
            try {
                Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($pivotPermission, $tableNames) {
                    $table->foreign($pivotPermission)
                        ->references('id')
                        ->on($tableNames['permissions'])
                        ->cascadeOnDelete();
                });
            } catch (\Exception $e) {}

            // model_has_roles table
            if (!Schema::hasColumn($tableNames['model_has_roles'], $teamKey)) {
                Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey, $pivotRole) {
                    $table->unsignedBigInteger($teamKey)->after($pivotRole);
                    $table->index($teamKey);
                });
            }
            
            try {
                Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($pivotRole) {
                    $table->dropForeign([$pivotRole]);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
                    $table->dropPrimary('model_has_roles_role_model_type_primary');
                });
            } catch (\Exception $e) {}
            
            try {
                Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey, $pivotRole) {
                    $table->primary([$teamKey, $pivotRole, 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
                });
            } catch (\Exception $e) {}
            
            try {
                Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($pivotRole, $tableNames) {
                    $table->foreign($pivotRole)
                        ->references('id')
                        ->on($tableNames['roles'])
                        ->cascadeOnDelete();
                });
            } catch (\Exception $e) {}
        }

        // 4. Remove company_id from users table
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamKey = $columnNames['team_foreign_key'] ?? 'team_id';

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
        });

        // Migrate back from company_user to users.company_id (approximation: take first company)
        $pivotData = DB::table('company_user')->get();
        foreach ($pivotData as $data) {
            DB::table('users')->where('id', $data->user_id)->update(['company_id' => $data->company_id]);
        }

        if (config('permission.teams')) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
                $table->dropUnique([$teamKey, 'name', 'guard_name']);
                $table->unique(['name', 'guard_name']);
                $table->dropColumn($teamKey);
            });

            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey) {
                $table->dropPrimary('model_has_permissions_permission_model_type_primary');
                $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
                $table->dropColumn($teamKey);
            });

            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey) {
                $table->dropPrimary('model_has_roles_role_model_type_primary');
                $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
                $table->dropColumn($teamKey);
            });
        }

        Schema::dropIfExists('company_user');
    }
};
