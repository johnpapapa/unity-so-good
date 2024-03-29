<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $events
 */
?>
<?php $this->assign('title', 'event archived'); ?>
<?php $this->assign('content-title', 'アーカイブ一覧'); ?>

<p class="note-p mb20">
    開催済みのイベントが表示されます
</p>

<a href="<?= $this->Url->build(['controller' => 'events','action' => 'index']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        イベント一覧に戻る
    </div>
</a>

<?php echo $this->element('event-item', array('events' => $events, 'displayResponseBtn' => false)); ?>
