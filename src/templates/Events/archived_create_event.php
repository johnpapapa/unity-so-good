<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $events
 */
?>
<?php $this->assign('title', 'event created'); ?>
<?php $this->assign('content-title', '開催済イベント一覧'); ?>

<p class="note-p mb20">
    開催した作成済イベントが表示されます。
</p>
<a href="<?= $this->Url->build(['controller' => 'events','action' => 'add']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        イベントの新規作成
    </div>
</a>

<a href="<?= $this->Url->build(['controller' => 'events','action' => 'deletedCreateEvent']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        削除済イベントの一覧
    </div>
</a>

<a href="<?= $this->Url->build(['controller' => 'events','action' => 'created']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        作成イベントの一覧
    </div>
</a>

<?php echo $this->element('event-item', array('events' => $events, 'displayCreatedBtn' => true)); ?>
