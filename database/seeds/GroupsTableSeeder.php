<?php

use Illuminate\Database\Seeder;
//use DB;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groups = [
        	[ 
        		'name' => 'Test Group',
        		'address' => 'Mirpur, Dhaka',
                'mobile_no' => '123456789',
                'responsible_person' => 'Mr Akash',
        		'email' => 'mr@test.com'
        	]
        ];
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('groups')->truncate();
        DB::table('groups')->insert($groups);

        $this->command->info('Successfully run group table seeder');
    }
}
