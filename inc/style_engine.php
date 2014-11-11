<?php
    function makeSearchResult($pid, $title, $user, $created_date){
        return "<div><a href='edit_page?pid=$pid'>$title</a>By <a href='profile.php?u=$user'></a> on $created_date</div>";
    }
?>