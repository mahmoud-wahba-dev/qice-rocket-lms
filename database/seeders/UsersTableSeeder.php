<?php
namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\User::updateOrCreate(['id' => 1], [
            'full_name' => 'admin',
            'mobile' => '09379332830',
            'role_name' => 'admin',
            'email' => env('ADMIN_EMAIL', 'admin@demo.com'),
            'role_id' => 2,
            'password' => User::generatePassword(env('ADMIN_PASSWORD', 'changeme')),
            'status' => 'active',
            'created_at' => '1597826952',
            'updated_at' => '1597826952',
        ]);
        \App\User::updateOrCreate(['id' => 2], ['full_name' => 'user', 'mobile' => '09379332831','role_name' => 'user', 'email' => 'user@gmail.com', 'role_id' => 1, 'password' => password_hash('123456', PASSWORD_BCRYPT),'status' => 'active','created_at' => '1597826952','updated_at' => '1597826952']);
        \App\User::updateOrCreate(['id' => 3], ['full_name' => 'teacher', 'mobile' => '09379332832','role_name' => 'teacher', 'email' => 'teacher@gmail.com', 'role_id' => 4, 'password' => password_hash('123456', PASSWORD_BCRYPT),'status' => 'active','created_at' => '1597826952','updated_at' => '1597826952']);
        \App\User::updateOrCreate(['id' => 4], ['full_name' => 'organ', 'mobile' => '09379332833','role_name' => 'organization', 'email' => 'organ@gmail.com', 'role_id' => 3, 'password' => password_hash('123456', PASSWORD_BCRYPT),'status' => 'active','created_at' => '1597826952','updated_at' => '1597826952']);
    }
}
