<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $participants_count_list
 */
?>
<?php $this->assign('title', 'admin event-list'); ?>
<?php $this->assign('content-title', '未反応数一覧'); ?>
<p class="note-p mb30">
    開催済みのイベントに対して未反応の数を表示。
    <br>イベントの開催日がユーザーの作成日より前だけカウント。
    <br>つまりユーザーが入る前のイベントはカウントされません。
    <br>(8日に作成したユーザーは9日のイベントをカウントしない)
</p>
<style>
    .event-list .header {
        background-color: darkgray;
    }
</style>
<?php
use Cake\Core\Configure;
?>

<div class="user-list">
<div class="user-list-content-header disp-flex just-center dir-row w100 mb10">
        <div class="w50 tc br">
            ID
        </div>
        <div class="w100 tc br">
            表示名
        </div>
        <div class="w100 tc br">
            未反応数
        </div>
        <div class="w100 tc">
            ---
        </div>
    </div>
    <?php foreach ($participants_count_list as $participants_count_data) : ?>
        <div class="user-list-content disp-flex just-center dir-row w100 mb30">
            <div class="user-id w50 tc">
                <?= $participants_count_data["id"] ?>
            </div>
            <div class="user-display-name w100 tc">
                <?= $participants_count_data["display_name"] ?>
            </div>
            <div class="user-cnt w100 tc">
                <?= $participants_count_data["cnt"] ?>
            </div>
            <div class="user-detail-page w100 tc">
                <a href="<?= $this->Url->build(['prefix'=>'Admin', 'controller' => 'administrators','action' => 'userDetail', $participants_count_data["id"]]); ?>">
                    <button>
                        詳細画面
                    </button>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>