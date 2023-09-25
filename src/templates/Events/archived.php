<?php $this->assign('title', 'event archived'); ?>
<?php $this->assign('content-title', 'アーカイブ一覧'); ?>
<?= $this->Html->css(['templates']) ?>

<p class="note-p mb20">
    開催済みのイベントが表示されます
</p>

<a href="<?= $this->Url->build(['controller' => 'events','action' => 'index']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        イベント一覧に戻る
    </div>
</a>

<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>
