<?php

use Illuminate\Database\Seeder;
//use DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [ 
        	[ 
        		'name' => 'Supper Admin',
                'slug' => 'super-admin'
        	], 
        	[ 
        		'name' => 'Admin',
                'slug' => 'admin'
        	],        	
        	[   
        		'name' => 'User',
                'slug' => 'user'
        	]
        ];

        DB::table('roles')->truncate();
        DB::table('roles')->insert($roles);

        $this->command->info('Successfully run roles table seeder');
    }
}
