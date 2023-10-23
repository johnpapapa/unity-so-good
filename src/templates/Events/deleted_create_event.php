<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $events
 */
?>
<?php $this->assign('title', 'event created'); ?>
<?php $this->assign('content-title', '削除済イベント一覧'); ?>

<p class="note-p mb20">
    削除したイベントが表示されます。
</p>
<a href="<?= $this->Url->build(['controller' => 'events','action' => 'add']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        イベントの新規作成
    </div>
</a>

<a href="<?= $this->Url->build(['controller' => 'events','action' => 'created']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        作成したイベントの一覧
    </div>
</a>

<?php echo $this->element('event-item', array('events' => $events, 'displayCreatedBtn' => true)); ?>
