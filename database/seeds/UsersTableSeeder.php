<?php

use Illuminate\Database\Seeder;
//use DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [ 
        	[ 
        		'name' => 'Mr super admin',    
                'mobile_no' => '+88017123456789',
                'address' => 'Mirpur, Dhaka',
        		'status' => 1,
        		'role_id' => 1,
        		'email' => 'super@admin.com',
        		'password' => bcrypt(123456),
                'factory_id' => 1
        	], 
        	[ 
        		'name' => 'Mr super admin',    
                'mobile_no' => '+88017123456789',
                'address' => 'Mirpur, Dhaka',
                'status' => 1,
                'role_id' => 2,
        		'email' => 'admin@admin.com',
        		'password' => bcrypt(123456),
                'factory_id' => 1
        	],        	
        	[   
        		'name' => 'Mr super admin',    
                'mobile_no' => '+88017123456789',
                'address' => 'Mirpur, Dhaka',
                'status' => 1,
                'role_id' => 3,
        		'email' => 'user@user.com',
        		'password' => bcrypt(123456),
                'factory_id' => 1
        	]
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();
        DB::table('users')->insert($users);

        $this->command->info('Successfully run users table seeder');
    }
}
