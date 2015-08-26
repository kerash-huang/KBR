<?php
function gget_date($key) {
    if(isset($_GET[$key]) && $_GET[$key]!="") {
        $ggetdate = $_GET[$key];
        if(preg_match("/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|[12][0-9]|3[01])$/",$ggetdate)) {
            return $_GET[$key];
        } else {
            return false;
        }
    } else {
        return false;
    }
}


function gpost_date($key) {
    if(isset($_POST[$key]) && $_POST[$key]!="") {
        $ggetdate = $_POST[$key];
        if(preg_match("/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|[12][0-9]|3[01])$/",$ggetdate)) {
            return $_POST[$key];
        } else {
            return false;
        }
    } else {
        return false;
    }
}