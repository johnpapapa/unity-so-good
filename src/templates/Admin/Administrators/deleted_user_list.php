<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $user_data
 */
?>
<?php $this->assign('title', 'admin deleted-user-list'); ?>
<?php $this->assign('content-title', '削除済ユーザーの一覧'); ?>
<style>
    .user-list .user-list-content-header {
        background-color: darkgray;
    }
</style>

<div class="user-list">
    <a href="<?= $this->Url->build(['controller' => 'administrators','action' => 'userList']); ?>">
        <div class="pure-button pure-u-1-2 mb30">
            ユーザーの一覧
        </div>
    </a>
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
