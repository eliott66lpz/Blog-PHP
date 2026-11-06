<?php
function showMsgErreur(?string $msg, string $color = 'red', ?string $style =null) : void{
    if(null === $msg) {
        return;
    }
        echo "<span style='color:$color'>" . ($style ==='italic' ? '<i>' :'') . $msg . ($style === 'italic' ? '</i>' :'') . "</span>";
}
?>