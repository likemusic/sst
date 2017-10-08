<?php

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
        factory(App\User::class, 5)->create();
        $user = new \App\User(['id' => 101, 'balance' => 1000]);
        $user->save();

        $user = new \App\User(['id' => 205, 'balance' => 1000]);
        $user->save();
    }
}
