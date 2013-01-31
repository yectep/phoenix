<?php

/**
 * The Courses class contains features and methods
 * to load multiple course data, teacher data and
 * class data.
 *
 * @author      Yectep Studios <info@yectep.hk>
 * @version     20930
 * @package     Phoenix
 *
 */
class Courses {

    /**
     * Returns an array of rooms available for use based on the rooms table.
     * @return mixed
     */
    static public function getRoomList() {

        $stmt = Data::query('SELECT * FROM `rooms` ORDER BY `RoomID` ASC, `RoomName` ASC');
        $intData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $retData = array();

        foreach($intData as $room) {
            $retData[$room["RoomID"]] = array(  "name"      => $room["RoomName"],
                                                "block"   => $room["RoomComment"]);
        }

        return $retData;

    }

    /**
     * Gets all applications based on a certain status
     */
    static public function getApps($status = 'submitted') {
        try {
            $stmt = Data::prepare('SELECT * FROM `applications` WHERE `AppStatus` = :status ORDER BY `AppLETS` DESC');
            $stmt->bindParam('status', $status);
            $stmt->execute();
            $courseRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }

        $courses = array();

        foreach($courseRaw as $app) {
            $infoJson = json_decode($app['AppFormJSON'], true);
            $classJson = json_decode($app['AppCourseJSON'], true);

            $student_count = 0;

            foreach($classJson as $class) {
                $student_count += $class['max_students'];
            }

            array_push($courses, array('app_id' => $app['AppID'],
                'program' => $infoJson['program'],
                'subject' => (($infoJson['program'] == 'SP') ? $infoJson['sp_subject'] : $infoJson['ap_subject']),
                'teacher_name' => $infoJson['teacher_name'],
                'teacher_email' => $infoJson['teacher_email'],
                'course_name' => $infoJson['course_name'],
                'count' => $student_count,
                'submitted' => date(DATETIME_SHORT, strtotime($app['AppLETS']))
            ));
        }

        return $courses;
    }

    /**
     * Gets and returns a data array (similar to load app) for a particular submitted application based on ID
     * @param   int $id
     */
    static public function getAppDataById($id) {
        try {
            $stmt = Data::prepare('SELECT * FROM `applications` WHERE `AppID` = :id AND `AppStatus` IN ("submitted", "inserted") LIMIT 1');
            $stmt->bindParam('id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);

        }

        if (!$data) {
            return false;
        }

        $ret['course_info'] = json_decode($data['AppFormJSON'], true);
        $ret['class_info'] = json_decode($data['AppCourseJSON'], true);
        $ret['contact_email'] = $data['AppEmail'];
        $ret['status'] = $data['AppStatus'];
        $ret['timestamps'] = array('created' => date(DATETIME_SHORT, strtotime($data['AppCTS'])), 'last_edit' => date(DATETIME_SHORT, strtotime($data['AppLETS'])));

        return $ret;
    }

    /**
     * Get name of subject
     * @param   string $code
     */
    static public function getSubjectName($code) {
        switch ($code) {
            case 'ARTS':
                return 'The Arts';
            break;
            case 'LANG':
                return 'Languages';
            break;
            case 'MSCT':
                return 'Maths, Science &amp; Technology';
            break;
            case 'PHED':
                return 'Physical Education';
            break;
            case 'IBTC':
                return 'IBDP Taster Course';
            break;
            case 'IBRV':
                return 'IBDP Review';
            break;
            case 'SATS':
                return 'SAT Subject Test Prep';
            break;
            case 'CISO':
                return 'CIS 101';
            break;
            case 'PATH':
                return 'Career Pathways';
            break;
        }
    }

    /**
     * Get list of courses
     */
    static public function getCourseList() {
        if (func_num_args() > 0)
            $subjects = func_get_args();
        else
            $subjects = array('ARTS', 'LANG', 'PHED', 'MSCT', 'IBRV', 'IBTC', 'CISO', 'PATH', 'SATS');
        $subjectText = '"'.implode('", "', $subjects).'"';
        
        try {
            $stmt = Data::query('SELECT * FROM `courses` WHERE `CourseSubj` IN ('.$subjectText.') ORDER BY `CourseTitle` ASC');
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get course classes
     */
    static public function getClassesOfCourseById($id) {
        try {
            $stmt = Data::prepare('SELECT `classes`.*, (SELECT count(`enrollment`.`EnrollID`) FROM `enrollment` WHERE `enrollment`.`ClassID` = `classes`.`ClassID` and `enrollment`.`EnrollStatus` = "enrolled") as `EnrollCount` FROM `classes` where `classes`.`CourseID` = :cid');
            $stmt->bindParam('cid', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }
    }

    /**
     * Get teacher info (email and display name)
     */
    static public function getTeacherById($id) {
        $stmt = Data::prepare('SELECT `staff`.`StaffName` as "TeacherName", `sso_objects`.`ObjEmail` as "TeacherEmail" FROM `staff`, `sso_objects` WHERE `sso_objects`.`ObjID` = `staff`.`ObjID` AND `staff`.`StaffID` = :sid');
        $stmt->bindParam('sid', $id, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (sizeof($res) == 1) {
            return $res[0];
        } else {
            return false;
        }
    }

}




?>