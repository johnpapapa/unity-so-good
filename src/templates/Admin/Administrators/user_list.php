<?php $this->assign('title', 'admin user-list'); ?>
<?php $this->assign('content-title', 'ユーザーの一覧'); ?>
<style>
    .user-list .user-list-content-header {
        background-color: darkgray;
    }
</style>

<div class="user-list">
    <div class="user-list-content-header disp-flex just-center dir-row w100 mb10">
        <div class="w50 tc br">
            ID
        </div>
        <div class="w100 tc br">
            表示名
        </div>
        <div class="w100 tc">
            ---
        </div>
    </div>
    <?php foreach($user_data as $user): ?>
        <div class="user-list-content disp-flex just-center dir-row w100 mb30">
            <div class="user-id w50 tc">
                <?= $user->id ?>
            </div>
            <div class="user-display-name w100 tc">
                <?= $user->display_name ?>
            </div>
            <div class="user-detail-page w100 tc">
                <a href="<?= $this->Url->build(['prefix'=>'Admin', 'controller' => 'administrators','action' => 'userDetail', $user->id]); ?>">
                    <button>
                        詳細画面
                    </button>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
