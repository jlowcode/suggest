<?php

$d = $displayData;

if (($d->view === 'details') && ($d->access)) {
    echo $d->incompleteButton;
    echo $d->button;
}
