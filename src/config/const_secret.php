<?php
    return [
        'param_linelogin' => [
            'client_id'     => '2000439541',
            'client_secret' => 'b3b4212b5b7760b442883bb88b1f21f1',
            'scope'         => 'profile%20openid',
            'redirect_uri' => [
                'unity.so-good.jp' => 'http://unity.so-good.jp/users/lineLogin', //http
                'unity-so-good.com' => 'http://unity-so-good.com/users/lineLogin', //https(ssl)
                'www.unity-so-good.com' => 'http://unity-so-good.com/users/lineLogin', //https(www)
                'localhost:8001' => 'http://localhost:8001/users/lineLogin' //(local)
            ]
        ]
    ];
?>