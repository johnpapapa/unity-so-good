<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $participants_count_list
 */
?>
<?php $this->assign('title', 'admin cancellation-list'); ?>
<?php $this->assign('content-title', '参加変更数一覧'); ?>
<p class="note-p mb30">
    イベント開始直前(1日前)に参加から不参加に変更したユーザーの数を表示。
    開催済みのイベントに対して未反応の数を表示。
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
            参加変更数
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