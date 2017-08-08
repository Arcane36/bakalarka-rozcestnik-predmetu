<?php


require_once($CFG->libdir . "/externallib.php");
require_once(dirname(__FILE__) . '/locallib.php');
define('ROLE_TEACHER', 3);
define('ROLE_TEACHER_NO_EDIT', 4);
define('ROLE_TEACHER_PRISPEVEK', 9);

class local_tul_ws_external extends external_api {
    public static function get_courses($shortname = '', $fullname = '') {
        global $CFG, $DB;
        $params = self::validate_parameters(self::get_courses_parameters(), array('shortname' => $shortname, 'fullname' => $fullname));
        $course_shortname = $params['shortname'];
        $course_fullname = $params['fullname'];
        if ( empty($course_shortname) && empty($course_fullname)) {
            throw new moodle_exception('erroremptycriteria','local_tul_ws');
        }
        $courses = $DB->get_records_sql("SELECT c.id, c.category, c.shortname, c.fullname, c.summary FROM {course} c 
                                  WHERE c.shortname LIKE '%" . $course_shortname . "%' AND c.fullname LIKE '%" . $course_fullname . "%' AND c.visible=1 ORDER BY c.shortname");
        
        
        $returns = array();
        $counter = 0;
        foreach ($courses as $course) {
            $course_context = context_course::instance($course->id, MUST_EXIST);
            $host_enrol = $DB->get_record('enrol', array('courseid'=>$course->id, 'status'=>ENROL_INSTANCE_ENABLED, 'enrol'=>'guest'));
            $returns[$counter]["courseid"] = $course->id;
            $returns[$counter]["courseshortname"] = $course->shortname;
            $returns[$counter]["coursefullname"] = $course->fullname;
            $returns[$counter]["coursedescription"] = $course->summary;
            $returns[$counter]["coursecategories"] = get_course_categories($course->category);
            $returns[$counter]["courseurl"] = (new moodle_url('/course/view.php', array('id' => $course->id)))->out();
            $returns[$counter]["coursefreeaccess"] = $host_enrol ? 1 : 0;
            $teachers = array();
            $cc = 0;
            $sqlUsers = 'SELECT u.firstname, u.lastname  
    					FROM {user} u, {role_assignments} ra 
    					WHERE ra.contextid = :contextid 
    						AND (ra.roleid = ' . ROLE_TEACHER . ' OR roleid = ' . ROLE_TEACHER_NO_EDIT . ' OR roleid = ' . ROLE_TEACHER_PRISPEVEK . ') 
    						AND ra.userid = u.id';
            $params = array('contextid' => $course_context->id);
            $users = $DB->get_records_sql($sqlUsers, $params);
            foreach ($users as $user) {
                $teachers[$cc]["firstname"] = $user->firstname; 
                $teachers[$cc]["lastname"] = $user->lastname;
                $cc++;
            }
            $returns[$counter]["teachers"] = $teachers;
            $counter++;
        }
        $site = get_site();
        $obj = new stdClass();
        $obj->sitename = $site->fullname;
        $obj->courses = $returns;
        return $obj;
    }
    
    /**
     * Parametry pro vyhledavani kurzu podle shortname / fullname
     * @return \external_function_parameters
     */
    public static function get_courses_parameters() {
        return new external_function_parameters(
            array(
                'shortname' => new external_value(PARAM_TEXT, 'course shortname', VALUE_DEFAULT,'', true),
                'fullname' => new external_value(PARAM_TEXT, 'course fullname', VALUE_DEFAULT, '',true),
            )
        );
    }
    
    /**
     * navratova struktura pro vyhledane kurzy
     * @return \external_multiple_structure
     */
    public static function get_courses_returns() {
        return new external_single_structure(
            array(
                'sitename' => new external_value(PARAM_TEXT, 'course shortname',VALUE_REQUIRED),
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid' => new external_value(PARAM_INT, 'course id'),
                            'courseshortname' => new external_value(PARAM_TEXT, 'course shortname'),
                            'coursefullname' => new external_value(PARAM_TEXT, 'course fullname'),
                            'coursedescription' => new external_value(PARAM_RAW, 'course description'),
                            'coursecategories' => new external_multiple_structure(
                                new external_value(PARAM_TEXT, 'course category')
                            ),
                            'courseurl' => new external_value(PARAM_TEXT, 'course URL'),
                            'coursefreeaccess' => new external_value(PARAM_INT, 'host access'),
                            'teachers' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                                        'lastname' => new external_value(PARAM_TEXT, 'lastname')
                                    )
                                ), 'users list', VALUE_REQUIRED)
                        )
                    )
                )
            )
        );
    }

}
