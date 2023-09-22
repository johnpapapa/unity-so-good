<?php $this->assign('title', 'event unresponded'); ?>
<?php $this->assign('content-title', '未表明イベント'); ?>
<p class="note-p mb20">
    未開催イベントのうち表明していないイベントが表示されます。<br>
    表明をすることで、この一覧にイベントが表示されなくなります。
</p>
<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>