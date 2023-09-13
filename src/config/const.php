<?php
    return [
        'event_states' => [
            0 => ["text"=>"未開催", "tag_color"=>"#FBEFC5"],
            1 => ["text"=>"開催中", "tag_color"=>"#BBDBF3"],
            2 => ["text"=>"開催済", "tag_color"=>"#BBBBBB"]
        ],
        'response_states' => [
            0 =>["text"=>"参加未定", "tag_color"=>"#C0D0D0", "icon"=>"?"],
            1 =>["text"=>"参加", "tag_color"=>"#a0e8b0", "icon"=>"o"],
            2 =>["text"=>"不参加", "tag_color"=>"#d1c580", "icon"=>"x"],
        ],
        'day_of_weeks' => [1=>'月',2=>'火',3=>'水',4=>'木',5=>'金',6=>'土',7=>'日'], //日付変換用の定数:i18nFormat()->dayOfWeek => 1~7
        'event_item_limit' => 50, //表示させるイベントの個数

        'param_linelogin' => [
            'client_id'     => '2000439541',
            'client_secret' => 'b3b4212b5b7760b442883bb88b1f21f1',
            'scope'         => 'profile%20openid',
            'redirect_uri' => [
                'unity.so-good.jp' => 'http://unity.so-good.jp/users/lineLogin',
                'localhost:8001' => 'http://localhost:8001/users/lineLogin'
            ]
        ],
        'cookie' => [
            'key' => 'key_auto_login',
            'expired' => '+1 month',
        ],
    ];
?>