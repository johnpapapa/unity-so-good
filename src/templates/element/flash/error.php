<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<style>
    .error {
        background-color: #ea8e8eaa;
        display: inline-block;
        padding: 10px;
        margin: 10px;
        border-radius: 3px;
        box-shadow: 0px 2px 2px 0px rgba(0, 0, 0, 0.5), inset 0px -3px 6px -2px rgba(0, 0, 0, 0.3);
    }
</style>
<div class="message error" onclick="this.classList.add('hidden');"><?= $message ?></div>
