<table class="display-flex ">
    <tr>
        <th>場所</th>
        <th>コート</th>
        <th>日付</th>
        <th>時間</th>
        <th>人数制限</th>
        <th>参加情報</th>
        <th>ユーザー参加情報</th>
        <th>参加表明</th>
        <th>参加者一覧</th>
    </tr>
    <tr>
        <td><?= $event->location->display_name ?>あああああああああ</td>
        <td><?= is_null($event->area) ? '' : $event->area . ',1,2,3,4,5,6,7,8,9コート' ?></td>
        <td><?= $event->date_y ?>年 <?= $event->date_m ?>月 <?= $event->date_m ?>日(<?= $event->day_of_week ?>)</td>
        <td><?= $event->start_time->i18nFormat('HH:mm'); ?> ~ <?= $event->end_time->i18nFormat('HH:mm');  ?></td>
        <td><?= $event->participants_limit <= 0 ? 'なし' : $event->participants_limit . '人' ?></td>
        <td>
            ?:<?= $event->participants_0_count ?>
            o:<?= $event->participants_1_count ?>
            x:<?= $event->participants_2_count ?>
        </td>
        <td>asdf</td>
        <td>
            <button>参加未定</button>
            <button>参加</button>
            <button>不参加</button>
        </td>
        <td>
            asldkfjaflkj
            asldkfjalkdsfj
            asdlkfjasldfkj
            asdlfkjasdlfkj
            dflkdfjlksj
            

        </td>
    </tr>
</table>


