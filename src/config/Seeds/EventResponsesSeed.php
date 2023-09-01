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
        $user_count = 150;
        $event_count = 1000;
        $event_response_count = 100;

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
                'display_name' => $this->display_name_lists()[$i],
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
                'user_id' => $faker->unique()->userName,
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
                // 'participants_limit' => $faker->randomDigitNotNull(),
                'participants_limit' => $faker->randomElement([-1, 8, 12, 16, 20, 24]),
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

    public function display_name_lists (){
        return [
        "神屋の森テニスコート",
        "横岳ヶ峰僧院テニスコート",
        "心浄実験場テニスコート",
        "阿蘇追禁猟区域テニスコート",
        "元長珍港テニスコート",
        "笠羚スラムテニスコート",
        "遊李伝道所テニスコート",
        "八戸関旧居テニスコート",
        "鎌田蔵隠し通路テニスコート",
        "紅坂街道テニスコート",
        "珠峯樹海テニスコート",
        "須藤薬苑テニスコート",
        "炎青ビルディングテニスコート",
        "桂川の森テニスコート",
        "櫛口主殿テニスコート",
        "響佳警察署テニスコート",
        "長岡台地テニスコート",
        "津島本拠地テニスコート",
        "茨前地下実験場テニスコート",
        "羚命中学校テニスコート",
        "引塩魔術陣テニスコート",
        "日吉津樹海テニスコート",
        "秋戸竜洞テニスコート",
        "祈初海底宮殿テニスコート",
        "塙町派出所テニスコート",
        "松阪湧水テニスコート",
        "北琥珀口自然保護エリアテニスコート",
        "由比地下空洞テニスコート",
        "等提岸テニスコート",
        "星李自然保護エリアテニスコート",
        "桐自小学校テニスコート",
        "尼子町県テニスコート",
        "絞玉線テニスコート",
        "大河植林場テニスコート",
        "横前海上要塞テニスコート",
        "西名連絡橋テニスコート",
        "船越廃聖堂テニスコート",
        "羨銅区テニスコート",
        "小増田河原テニスコート",
        "釜場鳥獣保護区テニスコート",
        "詩杜山地テニスコート",
        "伶果門テニスコート",
        "堀澤岬大木テニスコート",
        "新郷角人間界テニスコート",
        "西刀ヶ浜地峡テニスコート",
        "天歓公領テニスコート",
        "本梵蒼山地テニスコート",
        "常田司教領テニスコート",
        "有葉港テニスコート",
        "菊崎山地テニスコート",
        ];
    }
}
