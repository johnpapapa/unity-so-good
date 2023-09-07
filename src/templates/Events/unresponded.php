<?php $this->assign('title', 'event unresponded'); ?>
<?php $this->assign('content-title', '未表明イベント'); ?>
<p class="p-note">
    未開催イベントのうち表明していないイベントが表示されます。
</p>
<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>