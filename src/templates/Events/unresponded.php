<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $events
 */
?>
<?php $this->assign('title', 'event unresponded'); ?>
<?php $this->assign('content-title', '未表明イベント'); ?>

<p class="note-p mb20">
    未開催イベントのうち表明していないイベントが表示されます。<br>
    表明をすることで、この一覧にイベントが表示されなくなります。
</p>

<?php echo $this->element('event-item', array('events' => $events, 'displayResponseBtn' => false)); ?>