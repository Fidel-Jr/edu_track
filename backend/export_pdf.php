<?php
session_start();
require '../db/connect.php';   // your PDO connection
require '../vendor/autoload.php'; // dompdf autoload (if using Composer)

use Dompdf\Dompdf;

if (isset($_POST["export_attendance_pdf"])) {
    // Fetch data (example: students with attendance rate)
    $sql = "SELECT s.first_name, s.last_name, 
                calculate_attendance_rate(
                    SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END),
                    COUNT(a.id)
                ) AS attendance_rate
            FROM students s
            INNER JOIN student_classes sc ON s.id = sc.student_id
            INNER JOIN attendance a ON sc.id = a.student_class_id
            WHERE sc.class_id = :class_id
            GROUP BY s.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":class_id" => $_SESSION["class_id"]]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build HTML
    $html = '<h2>Class Attendance Report</h2>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
    $html .= '<thead><tr>
                <th>Name</th>
                <th>Attendance Rate (%)</th>
            </tr></thead><tbody>';

    foreach ($rows as $row) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</td>';
        $html .= '<td>' . $row['attendance_rate'] . '%</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    // Init Dompdf
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Stream PDF to browser (download)
    $dompdf->stream("attendance_report.pdf", ["Attachment" => true]);
}

if (isset($_POST["export_grades_pdf"])) {
    // Fetch overall grades from DB
    $sql = "
        SELECT 
            s.id AS student_id,
            CONCAT(s.first_name, ' ', s.last_name) AS student_name,
            sc.class_id,
            calculate_overall_grade(AVG(g.percentage)) AS overall_grade_percentage
        FROM student_classes sc
        JOIN students s ON sc.student_id = s.id
        LEFT JOIN grades g ON g.student_class_id = sc.id
        WHERE sc.class_id = :class_id
        GROUP BY s.id, sc.class_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":class_id" => $_SESSION["class_id"]]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build HTML for PDF
    $html = "
        <h2 style='text-align:center;'>Overall Grades Report</h2>
        <table border='1' cellspacing='0' cellpadding='8' width='100%'>
            <thead>
                <tr style='background:#f2f2f2;'>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Overall Grade (%)</th>
                </tr>
            </thead>
            <tbody>
    ";

    foreach ($rows as $row) {
        $html .= "
            <tr>
                <td>{$row['student_id']}</td>
                <td>{$row['student_name']}</td>
                <td>" . ($row['overall_grade_percentage'] !== null ? $row['overall_grade_percentage'] . "%" : "N/A") . "</td>
            </tr>
        ";
    }

    $html .= "
            </tbody>
        </table>
    ";

    // Generate PDF
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("overall_grades.pdf", ["Attachment" => 1]); // 1 = download, 0 = open in browser
    exit;
}
