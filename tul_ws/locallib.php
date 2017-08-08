<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . "/coursecatlib.php");

function get_course_categories($cat_id) {
    $ret = array();
    try {
        $category = coursecat::get($cat_id);
        $path = $category->path;
        $path = substr($path, 1);
        $patharray = explode('/', $path);
        $cc = coursecat::get_many($patharray);
        foreach ($cc as $c) {
            array_push($ret, $c->name);
        }
    } catch (Exception $exc) {
        $ret = array();
    }
    return $ret;
}
