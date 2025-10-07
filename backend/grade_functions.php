<?php 

    include '../db/connect.php';
    session_start();
    if ((!isset($_SESSION["username"]) && !isset($_SESSION["user_id"])) || !isset($_SESSION["class_id"])) {
        header("Location: ../welcome.php");
        exit;
    }

    // --- Modularized Functions ---
    function getGradeFilters(&$params) {
        $whereClauses = ["student_classes.class_id = :class_id"];
        $activity = isset($_GET['activity']) ? $_GET['activity'] : '';
        $gradeRange = isset($_GET['grade_range']) ? $_GET['grade_range'] : '';
        $student = isset($_GET['student']) ? $_GET['student'] : '';
        if (!empty($activity)) {
            $whereClauses[] = "grades.activity_type = :activity";
            $params[':activity'] = $activity;
        }
        if (!empty($gradeRange)) {
            [$min, $max] = explode('-', $gradeRange);
            $whereClauses[] = "grades.percentage BETWEEN :minGrade AND :maxGrade";
            $params[':minGrade'] = (int)$min;
            $params[':maxGrade'] = (int)$max;
        }
        if (!empty($student)) {
            $whereClauses[] = "(students.first_name LIKE :student OR students.last_name LIKE :student)";
            $params[':student'] = "%" . $student . "%";
        }
        return implode(" AND ", $whereClauses);
    }

    function fetchClassName($pdo, $classId) {
        $stmt = $pdo->prepare("SELECT course_name FROM class WHERE id = :class_id");
        $stmt->execute([':class_id' => $classId]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);
        return $class ? $class["course_name"] : '';
    }

    function getGradesPagination() {
        $gradesPage = isset($_GET['grades_page']) && is_numeric($_GET['grades_page']) ? (int) $_GET['grades_page'] : 1;
        $recordsPerPage = 10;
        $offset = ($gradesPage - 1) * $recordsPerPage;
        return [$gradesPage, $recordsPerPage, $offset];
    }

    function fetchGradesCount($pdo, $whereSQL, $params) {
        $countSql = "SELECT COUNT(*)
            FROM grades
            INNER JOIN student_classes ON grades.student_class_id = student_classes.id
            INNER JOIN students ON student_classes.student_id = students.id
            WHERE $whereSQL";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        return $countStmt->fetchColumn();
    }

    function fetchGradesRows($pdo, $whereSQL, $params, $recordsPerPage, $offset) {
        $sql = "SELECT 
                students.id AS student_id, 
                students.first_name, 
                students.last_name, 
                grades.*
                FROM grades
                INNER JOIN student_classes ON grades.student_class_id = student_classes.id
                INNER JOIN students ON student_classes.student_id = students.id
                WHERE $whereSQL
                ORDER BY grades.date DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    $params = [":class_id" => $_SESSION["class_id"]];
    $whereSQL = getGradeFilters($params);
    $className = fetchClassName($pdo, $_SESSION["class_id"]);
    list($gradesPage, $recordsPerPage, $offset) = getGradesPagination();
    $totalGradeRows = fetchGradesCount($pdo, $whereSQL, $params);
    $totalGradesPages = ceil($totalGradeRows / $recordsPerPage);
    $students = fetchGradesRows($pdo, $whereSQL, $params, $recordsPerPage, $offset);
    $gradesQueryString = http_build_query(array_merge($_GET, ['grades_page' => null]));

?>