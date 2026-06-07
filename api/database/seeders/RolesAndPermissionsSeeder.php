<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'oficios.ver',
            'oficios.criar',
            'oficios.editar',
            'oficios.excluir',
            'usuarios.ver',
            'usuarios.criar',
            'usuarios.editar',
            'usuarios.excluir',
            'contatos.ver',
            'contatos.criar',
            'contatos.editar',
            'contatos.excluir',
            'templates.ver',
            'templates.criar',
            'templates.editar',
            'templates.excluir',
            'configuracoes.acessar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(
            ['name' => 'Administrador', 'guard_name' => 'web'],
            ['description' => 'Acesso total ao sistema', 'status' => 'Ativo']
        );
        $admin->syncPermissions($permissions);

        $usuario = Role::firstOrCreate(
            ['name' => 'Usuário Padrão', 'guard_name' => 'web'],
            ['description' => 'Acesso básico para criação e visualização', 'status' => 'Ativo']
        );
        $usuario->syncPermissions([
            'oficios.ver',
            'oficios.criar',
            'contatos.ver',
            'contatos.criar',
            'templates.ver',
        ]);

        $viewer = Role::firstOrCreate(
            ['name' => 'Visualizador', 'guard_name' => 'web'],
            ['description' => 'Apenas visualização de registros', 'status' => 'Ativo']
        );
        $viewer->syncPermissions([
            'oficios.ver',
            'contatos.ver',
            'templates.ver',
        ]);
    }
}
