<?php $this->assign('title', 'event index'); ?>
<?php $this->assign('content-title', 'イベント一覧'); ?>
<?= $this->Html->css(['templates']) ?>

<p class="note-p mb20">
    14日前までのイベントが表示されます
</p>

<a class="nostyle-a" href="<?= $this->Url->build(['controller' => 'events','action' => 'archived']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        イベントのアーカイブ
    </div>
</a>

<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>
