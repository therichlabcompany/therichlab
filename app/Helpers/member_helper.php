<?php

function member_status_label($status)
{
    $map = [
        'ACTIVE' => ['정상', 'success'],
        'BLOCK'  => ['차단', 'danger'],
        'WAIT'   => ['대기', 'warning'],
        'LEAVE'  => ['탈퇴', 'secondary'],
    ];

    return $map[$status] ?? [$status, 'secondary'];
}