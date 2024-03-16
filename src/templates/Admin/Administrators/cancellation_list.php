<?php

/**
 * @var \App\View\AppView $this
 * @var mixed $participants_count_list
 */
?>
<?php $this->assign('title', 'admin cancellation-list'); ?>
<?php $this->assign('content-title', '参加変更数一覧'); ?>
<p class="note-p mb30">
    イベント開始直前(1日前)に参加から不参加に変更したユーザーの数を表示。<br>
    押し間違えで参加から不参加にした人も表示されちゃうので要確認。<br>
</p>
<style>
    .event-list .header {
        background-color: darkgray;
    }
</style>
<?php

use Cake\Core\Configure;
?>

<div class="user-list w100 mb10">
    <?php foreach ($responder_grouped_by_event as $responder_group) : ?>
        <div class="user-list-content disp-flex just-center dir-row w100 pb10">
            <div class="w50 tc" style="flex-wrap:wrap">
                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'administrators', 'action' => 'eventDetail', $responder_group["event_id"]]); ?>">
                    <button class="w100">
                        詳細
                    </button>
                </a>
            </div>
            <div class="event-location just-center align-center disp-flex w100 tc bold" style="flex-wrap:wrap">
                <?= $responder_group["location_name"] ?>
            </div>

            <div class="event-date w100 tc">
                <?= $responder_group["event_date"] . "<br>" . $responder_group["event_start_time"] . "-" . $responder_group["event_end_time"] ?>
            </div>
        </div>

        <div class="latest-modified-list mb30">
            <?php foreach ($responder_group["latest_modified_list"] as $responder) : ?>
                <div class="disp-flex just-center w100" style="border: black 1px solid;">
                    <div class="w50 disp-flex just-center align-center tc" style="border-right: black 1px solid;">
                        <?= $responder["responder_name"] ?>
                    </div>

                    <div class="w50 tc disp-flex just-end" style="text-wrap: nowrap;">
                        <table>
                            <tr>
                                <th style="width:75px">状態</th>
                                <th>変更日時</th>
                            </tr>
                            <?php foreach ($responder["logs"] as $logs) : ?>
                                <tr>
                                    <td><?= Configure::read('response_states')[$logs->response_state]["text"] ?></td>
                                    <td><?= $logs->created_at->i18nFormat('MM/dd HH:mm:ss') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        
    <?php endforeach; ?>
</div>