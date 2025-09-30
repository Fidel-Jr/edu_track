<?php 

    function getGradeAndColor($percentage) {
        if ($percentage >= 81) {
            return ["A", "text-success"];
        } elseif ($percentage > 74) {
            return ["C", "text-warning"];
        } else {
            return ["F", "text-danger"];
        }
    }

?>