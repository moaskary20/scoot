<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء المستخدم الرئيسي
        $user = User::firstOrCreate(
            ['email' => 'mo.askary@gmail.com'],
            [
                'name' => 'Mohamed Askary',
                'email' => 'mo.askary@gmail.com',
                'password' => Hash::make('newpassword'),
                'phone' => null,
                'wallet_balance' => 0,
                'loyalty_points' => 0,
                'loyalty_level' => 'bronze',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // تحديث كلمة المرور إذا كان المستخدم موجوداً بالفعل
        if ($user->wasRecentlyCreated === false) {
            $user->update([
                'password' => Hash::make('newpassword'),
                'is_active' => true,
            ]);
        }

        // إضافة دور Admin للمستخدم إذا كان موجوداً
        $adminRole = Role::where('name', 'admin')->orWhere('name', 'Admin')->first();
        if ($adminRole && !$user->roles->contains($adminRole->id)) {
            $user->roles()->attach($adminRole->id);
        }

        $this->command->info('تم إنشاء المستخدم بنجاح!');
        $this->command->info('Email: mo.askary@gmail.com');
        $this->command->info('Password: newpassword');
    }
}
