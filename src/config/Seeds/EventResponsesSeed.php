<?php
declare(strict_types=1);

use Migrations\AbstractSeed;
use Cake\Log\Log;

/**
 * EventResponses seed.
 */
class EventResponsesSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run(): void
    {
        $location_count = 30;
        $user_count = 100;
        $event_count = 100;
        $event_response_count = 70;

        // $user_id_between_min = 1;
        // $user_id_between_max = $user_count;
        $location_id_between_min = 1;
        $location_id_between_max = $location_count;
        $event_id_between_min = 1;
        $event_id_between_max = $event_count;
        

        $event_datetime_min = strtotime('2023-1-1 00:00:00');
        $event_datetime_max = strtotime('2023-12-31 00:00:00');
        
        $faker = Faker\Factory::create('ja_JP');

        //locations
        $location_data = [];
        for($i = 1; $i < $location_count+1; $i++){
            $prc = $faker->prefecture(); 
            $cit = $faker->city(); 
            $sta = $faker->streetAddress();
            $location_data[] = [
                'display_name' => $faker->company,
                'address' => $prc.$cit.$sta,
                'usage_price' => $faker->numberBetween($min = 1000, $max = 9000),
                'night_price' => $faker->numberBetween($min = 200, $max = 1000),
            ];
        }
        $location_table = $this->table('locations');
        $location_table->insert($location_data)->save();

        //users
        $user_data = [];
        for($i = 1; $i < $user_count+1; $i++){
            $user_data[] = [
                'display_name' => $faker->unique()->name,
                'username' => $faker->unique()->userName,
                'line_user_id' => null,
                'password' => null,
                'remember_token' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        $user_table = $this->table('users');
        $user_table->insert($user_data)->save();
        

        //events
        $event_data = [];
        $event_response_data = [];
        for($event_i = 1; $event_i < $event_count+1; $event_i++){
            $val = rand($event_datetime_min, $event_datetime_max);
            $start_time = date('Y-m-d H:i:s', $val);
            $end_time = date("Y-m-d H:i:s",strtotime($start_time . "+2hour"));
            $event_data[] = [
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'area' => $faker->randomDigitNotNull(),
                'participants_limit' => $faker->randomDigitNotNull(),
                'comment' => $faker->realText(),
                'organizer_id' => $faker->numberBetween(1, $user_count),
                'location_id' => $faker->numberBetween(1, $location_count),
            ];
            

            
            for($i = 1; $i < random_int(intdiv($event_response_count,2) ,$event_response_count); $i++){
                $event_response_data[] = [
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'response_state' => $faker->numberBetween(0, 2),
                    'responder_id' => $i,
                    'event_id' => $event_i
                    // 'event_id' => $faker->numberBetween($event_id_between_min, $event_id_between_max),
                ];
            }
        }
        $event_table = $this->table('events');
        $event_table->insert($event_data)->save();
        

        $event_response_table = $this->table('event_responses');
        $event_response_table->insert($event_response_data)->save();
        

        // $table = $this->table('events');
        // $table->insert($data)->save();

        //event_reseponses
        // $data = [];
        
        // for($i = 0; $i < $event_response_count; $i++){
        //     $data[] = [
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s'),
        //         'response_state' => $faker->numberBetween(0, 2),
        //         'responder_id' => $faker->numberBetween($user_id_between_min, $user_id_between_max),
        //         'event_id' => $faker->numberBetween($event_id_between_min, $event_id_between_max),
        //     ];
        // }
        // $table = $this->table('event_responses');
        // $table->insert($data)->save();

    }
}
