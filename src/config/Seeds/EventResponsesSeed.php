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
        
        $event_datetime_min = strtotime('2023-1-1 00:00:00');
        $event_datetime_max = strtotime('2023-12-31 00:00:00');
        
        $faker = Faker\Factory::create('ja_JP');

        //locations
        $location_data = $this->gen_location_data($faker, $location_count);
        $location_table = $this->table('locations');
        $location_table->insert($location_data)->save();

        //users
        $user_data = $this->gen_user_data($faker, $user_count);
        $user_table = $this->table('users');
        $user_table->insert($user_data)->save();

        //events
        [$event_data, $event_response_data] = $this->gen_event_data(
            $faker, 
            $event_count, 
            $user_count, 
            $location_count, 
            $event_datetime_min, 
            $event_datetime_max
        );

        $event_table = $this->table('events');
        // $event_table->insert($event_data)->save();
        

        $chunk_count = 12; //event_responseは多すぎるので分割
        $chunk_size = intdiv(count($event_response_data), $chunk_count);        
        $event_response_datas = array_chunk($event_response_data, $chunk_size);
        $event_response_table = $this->table('event_responses');
        foreach($event_response_datas as $event_response_data){
            // $event_response_table->insert($event_response_data)->save();
        }
    }

    public function location_display_name_lists ($count){
        $display_name_lists = [
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
        shuffle($display_name_lists);
        return array_slice($display_name_lists, 0, $count);
    }

    public function gen_location_data($faker, $location_count){
        $location_data = [];
        $display_name_lists = $this->location_display_name_lists($location_count);
        for($l_i = 0; $l_i < $location_count; $l_i++){
            $prc = $faker->prefecture(); 
            $cit = $faker->city(); 
            $sta = $faker->streetAddress();
            $location_data[] = [
                'display_name' => $display_name_lists[$l_i],
                'address' => $prc.$cit.$sta,
                'usage_price' => $faker->numberBetween($min = 1000, $max = 9000),
                'night_price' => $faker->numberBetween($min = 200, $max = 1000),
            ];
        }
        return $location_data;
    }

    public function gen_user_data($faker, $user_count){
        $user_data = [];
        for($u_i = 0; $u_i < $user_count; $u_i++){
            $idx = $u_i % 3;
            if ($idx == 0) { $display_name = $faker->unique()->name; }
            elseif ($idx == 1) { $display_name = $faker->unique()->kanaName; }
            elseif ($idx == 2) { $display_name = $faker->unique()->userName; }
            
            $user_data[] = [
                'display_name' => $display_name,
                'user_id' => null,
                'line_user_id' => null,
                'password' => null,
                'remember_token' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        return $user_data;
    }

    public function gen_event_data($faker, $event_count, $user_count, $location_count, $event_datetime_min, $event_datetime_max){
        $event_data = [];
        $event_response_data = [];
        for($e_idx = 0; $e_idx < $event_count; $e_idx++){
            $event_id = $e_idx+1;
            $val = rand($event_datetime_min, $event_datetime_max);
            $start_time = date('Y-m-d H:i:s', $val);
            $time_distance = ['+2hour', '+4hour'][random_int(0, 1)];
            $end_time = date("Y-m-d H:i:s",strtotime($start_time . $time_distance)); //開始時間の2時間後

            /*$event_data[]*/ $data = [
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'area' => $faker->randomDigitNotNull(),
                'participants_limit' => $faker->randomElement([-1, 8, 12, 16, 20, 24]), //人数制限はコート数だけ
                'comment' => $faker->realText(),
                'organizer_id' => $faker->numberBetween(1, $user_count),
                'location_id' => $faker->numberBetween(1, $location_count),
            ];
            $event_table = $this->table('events');
            $event_table->insert($data)->save();
            $event_response_data = array_merge(
                $event_response_data, 
                $this->gen_event_response_data($faker, $user_count, $event_id, $start_time)
            );
        }
        return [$event_data, $event_response_data];
    }

    public function gen_event_response_data($faker, $user_count, $event_id, $start_time){
        $event_response_data = [];
        $rand_val = random_int(0, $user_count); //eventに反応した人数の生成
        $event_response_count = ($rand_val % 2 == 0) ? $rand_val : $rand_val+1; //event反応人数はペアができるように
        $event_response_count = ($rand_val > $user_count) ? $event_response_count - 1 : $event_response_count;
        
        $rand_ids = range(1, $event_response_count); //eventに反応した人はuser全体から反応人数分ランダム
        shuffle($rand_ids);
        $responder_ids = array_slice($rand_ids, 0, $event_response_count);

        for($er_idx=0; $er_idx < $event_response_count; $er_idx++){
            $response_time = rand(strtotime($start_time), strtotime($start_time . "-7day")); //反応時間はevent開始時間から7日前までの範囲
            $response_time = date("Y-m-d H:i:s", $response_time);
            
            $event_response_data[] = [
                'created_at' => $response_time,
                'updated_at' => $response_time,
                'response_state' => $faker->numberBetween(0, 2),
                'responder_id' => $responder_ids[$er_idx],
                'event_id' => $event_id
            ];
        }
        $event_response_table = $this->table('event_responses');
        $event_response_table->insert($event_response_data)->save();
        return $event_response_data;
    }

}
