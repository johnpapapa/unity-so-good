<?php
    return [
        'event_states' => [
            0 => ["text"=>"未開催", "tag_color"=>"#FBEFC5"],
            1 => ["text"=>"開催中", "tag_color"=>"#fff"],
            2 => ["text"=>"開催済", "tag_color"=>"#fff"]
        ],
        'response_states' => [
            0 =>["text"=>"参加未定", "tag_color"=>"#C0D0D0"],
            1 =>["text"=>"参加", "tag_color"=>"#BBDBF3"],
            2 =>["text"=>"不参加", "tag_color"=>"#fff"],
        ],
        'day_of_weeks' => ['月','火','水','木','金','土','日'], //日付変換用の定数

    ];
?>