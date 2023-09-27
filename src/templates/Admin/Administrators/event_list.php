<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[]|\Cake\Collection\CollectionInterface $events
 */
?>
<?php $this->assign('title', 'admin event-list'); ?>
<?php $this->assign('content-title', 'イベントの一覧'); ?>

<style>
    .event-list .header {
        background-color: darkgray;
    }
</style>
<?php

use Cake\Core\Configure;

$day_of_weeks = Configure::read('day_of_weeks');
?>

<div class="event-list">
    <?php foreach ($events as $event) : ?>
        <div class="event-list-content mb30">
            <div class="disp-flex just-center dir-column w100">
                <div class="disp-flex just-center w100">
                    <div class="header tc" style="width:100px;">
                        ID
                    </div>
                    <div class="w100">
                        <?= $event->id ?>
                    </div>
                </div>
            </div>
            <div class="disp-flex just-center dir-column w100">
                <div class="disp-flex just-center w100">
                    <div class="header tc" style="width:100px;">
                        状態
                    </div>
                    <div class="w100">
                        <?= ($event->deleted_at) ? "削除済(非公開)" : "公開中" ?>
                    </div>
                </div>
            </div>
            <div class="disp-flex just-center dir-column w100">
                <div class="disp-flex just-center w100 over-ellipsis">
                    <div class="header tc" style="width:100px;">
                        場所
                    </div>
                    <div class="w100">
                        <?= $event->location->display_name ?>
                    </div>
                </div>
            </div>

            <div class="disp-flex just-center dir-column w100">
                <div class="disp-flex just-center w100">
                    <div class="header tc" style="width:100px;">
                        コート
                    </div>
                    <div class="w100">
                        <?= $event->area ?>
                    </div>
                </div>
            </div>

            <div class="disp-flex just-center dir-column w100">
                <div class="disp-flex just-center w100">
                    <div class="header tc" style="width:100px;">
                        開催日
                    </div>
                    <div class="w100">
                        <?= $event->start_time->i18nFormat('yyyy-MM-dd'); ?>
                        (<?= $day_of_weeks[$event->start_time->dayOfWeek] ?>)
                    </div>
                </div>
            </div>

            <div class="disp-flex just-center dir-column w100">
                <div class="disp-flex just-center w100">
                    <div class="header tc" style="width:100px;">
                        時刻
                    </div>
                    <div class="w100">
                        <?= $event->start_time->i18nFormat('HH:mm'); ?> ~ <?= $event->end_time->i18nFormat('HH:mm');  ?>
                    </div>
                </div>
            </div>

            <div class="disp-flex just-center dir-column w100">
                <div class="disp-flex just-center w100">
                    <div class="header tc" style="width:100px;">
                        ---
                    </div>
                    <div class="w100">
                        <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'administrators', 'action' => 'eventDetail', $event->id]); ?>">
                            <button class="w100">
                                詳細
                            </button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>