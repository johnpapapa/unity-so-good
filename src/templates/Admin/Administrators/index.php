<?php $this->assign('title', 'admin index'); ?>
<?php $this->assign('content-title', '管理画面ホーム'); ?>


<div class="disp-flex just-center align-center dir-column w100">
    <div class="mb30">
        <a href="<?= $this->Url->build(['prefix'=>'Admin', 'controller' => 'administrators','action' => 'user-list']); ?>">
            <button class="pure-button">
                ユーザーの一覧
            </button>
        </a>
    </div>

    <div>
        <a href="<?= $this->Url->build(['prefix'=>'Admin', 'controller' => 'administrators','action' => 'event-list']); ?>">
            <button class="pure-button">
                イベントの一覧
            </button>
        </a>
    </div>
</div>