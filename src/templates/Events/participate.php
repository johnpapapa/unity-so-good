<?php $this->assign('title', 'event participate'); ?>
<?php $this->assign('content-title', '参加予定イベント'); ?>
<p class="note-p mb20">
    今日以降の参加/参加未定イベントが表示されます。
</p>
<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>