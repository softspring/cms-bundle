<?php

if (isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['SFS_CMS_REQUEST_URI'] = $_SERVER['REQUEST_URI'];
}
