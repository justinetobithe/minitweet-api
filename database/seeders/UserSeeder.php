<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name'  => 'Anna Beach',
                'email' => 'anna.beach@minitweet.test',
            ],
            [
                'name'  => 'John Doe',
                'email' => 'john.doe@minitweet.test',
            ],
            [
                'name'  => 'James Brown',
                'email' => 'james.brown@minitweet.test',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name'     => $userData['name'], 
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
