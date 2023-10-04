<?php
/**
 * @var \App\View\AppView $this
 * @var array $information_data
 * @var mixed $is_admin
 */
?>
<?php $this->assign('title', 'information about'); ?>
<?php $this->assign('content-title', '概要'); ?>
<link href="https://use.fontawesome.com/releases/v6.4.2/css/all.css" rel="stylesheet">

<h3 class="mb10">概要</h3>
<div class="mb30">
    <?= nl2br(h($information_data["about"])) ?>
</div>

<h3 class="mb10">ルール</h3>
<div class="mb30">
    <?= nl2br(h($information_data["rule"])) ?>
</div>

<h3 class="mb10" id="contact">連絡先</h3>
<div class="disp-flex just-center dir-column" style="font-size: 1.2em; row-gap: 10px;">
    <a class="disp-iblock" href="https://www.instagram.com/unity.s.tennis/">
        <div class="disp-flex align-center dir-row" style="column-gap: 10px;">
            <div><i class="fab fa-instagram" style="font-size: 1.5em;"></i></div>
            <div>インスタグラム</div>
        </div>
    </a>

    <a class="disp-iblock" href="https://twitter.com/UnitySoftTennis">
        <div class="disp-flex align-center dir-row" style="column-gap: 10px;">
            <div><i class="fa-brands fa-x-twitter" style="font-size: 1.5em;"></i></div>
            <div>Twitter</div>
        </div>
    </a>

    <a class="disp-iblock" href="https://www.net-menber.com/look/data/155800.html">
        <div class="disp-flex align-center dir-row" style="column-gap: 10px;">
            <i class="fa-solid fa-face-smile" style="font-size: 1.5em;"></i>
            <div>スポーツやろうよ</div>
        </div>
    </a>
</div>