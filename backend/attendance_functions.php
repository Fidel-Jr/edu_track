<?php

    include "../db/connect.php";
    session_start();
    if ((!isset($_SESSION["username"]) && !isset($_SESSION["user_id"])) || !isset($_SESSION["class_id"])) {
        header("Location: ../welcome.php");
        exit;
    }

    // --- Modularized Functions ---
    function getAttendanceFilters(&$params) {
        $filters = [];
        if (!empty($_GET['day'])) {
            $filters[] = "DAY(attendance.date) = :day";
            $params[':day'] = (int)$_GET['day'];
        }
        if (!empty($_GET['month'])) {
            $filters[] = "MONTH(attendance.date) = :month";
            $params[':month'] = (int)$_GET['month'];
        }
        if (!empty($_GET['status'])) {
            $filters[] = "attendance.status = :status";
            $params[':status'] = $_GET['status'];
        }
        if (!empty($_GET['student'])) {
            $filters[] = "(students.first_name LIKE :student OR students.last_name LIKE :student)";
            $params[':student'] = "%" . $_GET['student'] . "%";
        }
        return $filters ? (" AND " . implode(" AND ", $filters)) : "";
    }

    function fetchClassName($pdo, $classId) {
        $stmt = $pdo->prepare("SELECT course_name FROM class WHERE id = :class_id");
        $stmt->execute([':class_id' => $classId]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);
        return $class ? $class["course_name"] : '';
    }

    function getAttendancePagination() {
        $attendancePage = isset($_GET['attendance_page']) && is_numeric($_GET['attendance_page']) 
            ? (int) $_GET['attendance_page'] : 1;
        $attendancePerPage = 10;
        $attendanceOffset = ($attendancePage - 1) * $attendancePerPage;
        return [$attendancePage, $attendancePerPage, $attendanceOffset];
    }

    function fetchAttendanceRows($pdo, $classId, $filterSql, $params, $attendancePerPage, $attendanceOffset) {
        $sql = "SELECT 
                    attendance.*, students.first_name, students.last_name, students.id AS student_id
                FROM attendance
                INNER JOIN student_classes ON attendance.student_class_id = student_classes.id
                INNER JOIN students ON student_classes.student_id = students.id
                WHERE student_classes.class_id = :class_id $filterSql
                ORDER BY attendance.date DESC, students.last_name ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $type = ($key === ':class_id' || $key === ':day' || $key === ':month') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->bindValue(':limit', $attendancePerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $attendanceOffset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function fetchAttendanceCount($pdo, $classId, $filterSql, $params) {
        $countSql = "SELECT COUNT(*) 
                    FROM attendance
                    INNER JOIN student_classes ON attendance.student_class_id = student_classes.id
                    INNER JOIN students ON student_classes.student_id = students.id
                    WHERE student_classes.class_id = :class_id $filterSql";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        return $countStmt->fetchColumn();
    }

    function fetchStudentList($pdo, $classId) {
        $sql = "SELECT * FROM students
                INNER JOIN student_classes ON students.id = student_classes.student_id
                WHERE student_classes.class_id = :class_id
                ORDER BY students.last_name, students.first_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':class_id' => $classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Main Script ---
    $params = [':class_id' => $_SESSION["class_id"]];
    $filterSql = getAttendanceFilters($params);
    $className = fetchClassName($pdo, $_SESSION["class_id"]);
    list($attendancePage, $attendancePerPage, $attendanceOffset) = getAttendancePagination();
    $totalAttendanceRows = fetchAttendanceCount($pdo, $_SESSION["class_id"], $filterSql, $params);
    $totalAttendancePages = ceil($totalAttendanceRows / $attendancePerPage);
    $attendanceRows = fetchAttendanceRows($pdo, $_SESSION["class_id"], $filterSql, $params, $attendancePerPage, $attendanceOffset);
    $queryString = http_build_query(array_merge($_GET, ['attendance_page' => null]));

?>