<?php $this->assign('title', 'event created'); ?>
<?php $this->assign('content-title', '作成済イベント一覧'); ?>
<p class="note-p mb20">
    追加したイベントが表示されます。
</p>
<a class="nostyle-a" href="<?= $this->Url->build(['controller' => 'events','action' => 'add']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        イベントの新規作成
    </div>
</a>

<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayCreatedBtn' => true)); ?>
<?php endforeach; ?>