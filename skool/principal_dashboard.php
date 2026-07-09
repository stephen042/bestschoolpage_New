<?php
/**
 * ============================================================================
 * PRINCIPAL ANALYTICS DASHBOARD - PHP 8.x CONVERTED
 * ============================================================================
 * Shows: KPIs, class performance, subject performance, class leaderboards
 * Version: 2.0 (PHP 8.x Compatible) - PDO Converted
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

// ============================================================================
// ACCESS CONTROL
// ============================================================================
// Only allow principal/admin access (usertype = 1)
if (empty($_SESSION['userid'])) {
    redirect(SKOOL_URL . 'login.php');
    exit;
}

if (!in_array((string)($_SESSION['usertype'] ?? ''), ['1', '0', 'admin'], true)) {
    redirect(SKOOL_URL . 'home.php');
    exit;
}

// principal_dashboard.php now acts as an alias route for dashboard.php
redirect('dashboard.php');
exit;

$school_id = $_SESSION['userid'];
$create_by_userid = $_SESSION['userid'] ?? 0;

// ============================================================================
// GET FILTERS FROM URL
// ============================================================================
$selected_session = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$selected_term = isset($_GET['term_id']) ? (int)$_GET['term_id'] : 0;
$selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// If no session selected, get the latest session
if ($selected_session == 0) {
    $sessionData = db_get_row("SELECT id FROM school_session WHERE create_by_userid = ? ORDER BY id DESC LIMIT 1", [$create_by_userid]);
    $selected_session = $sessionData['id'] ?? 0;
}

// If no term selected, get first term
if ($selected_term == 0) {
    $termData = db_get_row("SELECT id FROM school_term WHERE create_by_userid = ? ORDER BY id LIMIT 1", [$create_by_userid]);
    $selected_term = $termData['id'] ?? 0;
}

// ============================================================================
// GET FILTER DATA
// ============================================================================
$sessions = db_get_rows("SELECT id, session FROM school_session WHERE create_by_userid = ? ORDER BY session DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT id, term FROM school_term WHERE create_by_userid = ? ORDER BY id", [$create_by_userid]);
$classes = db_get_rows("SELECT id, name FROM school_class WHERE create_by_userid = ? ORDER BY name", [$create_by_userid]);

// ============================================================================
// KPI: TOTAL STUDENTS (Male/Female breakdown)
// ============================================================================
$students_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN gender = 'Male' OR gender = 'M' THEN 1 ELSE 0 END) as male,
                    SUM(CASE WHEN gender = 'Female' OR gender = 'F' THEN 1 ELSE 0 END) as female
                    FROM manage_student WHERE create_by_userid = ?";
$students_data = db_get_row($students_query, [$create_by_userid]);
if (empty($students_data)) $students_data = ['total' => 0, 'male' => 0, 'female' => 0];

// ============================================================================
// KPI: TOTAL STAFF (Male/Female breakdown)
// ============================================================================
$staff_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN gender = 'Male' OR gender = 'M' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN gender = 'Female' OR gender = 'F' THEN 1 ELSE 0 END) as female
                FROM staff_manage WHERE create_by_userid = ?";
$staff_data = db_get_row($staff_query, [$create_by_userid]);
if (empty($staff_data)) $staff_data = ['total' => 0, 'male' => 0, 'female' => 0];

// ============================================================================
// KPI: TOTAL SUBJECTS
// ============================================================================
$subjects_data = db_get_val("SELECT COUNT(*) FROM school_subject WHERE create_by_userid = ?", [$create_by_userid]);

// ============================================================================
// CLASS PERFORMANCE
// ============================================================================
$class_performance = [];
if ($selected_session > 0 && $selected_term > 0) {
    $class_query = "SELECT sc.id, sc.name as class_name,
                    COUNT(DISTINCT ms.id) as student_count,
                    ROUND(AVG(CAST(ist.score AS DECIMAL(5,2))), 1) as avg_score
                    FROM school_class sc
                    LEFT JOIN manage_student ms ON sc.id = ms.class AND ms.create_by_userid = ?
                    LEFT JOIN input_score_class_teacher ist ON ms.id = ist.student_id 
                        AND ist.session_id = ? AND ist.term_id = ?
                        AND ist.class_id = sc.id
                    WHERE sc.create_by_userid = ?
                    GROUP BY sc.id
                    ORDER BY avg_score DESC";
    
    $class_performance = db_get_rows($class_query, [$create_by_userid, $selected_session, $selected_term, $create_by_userid]);
}

// Add position to class performance
$position = 1;
foreach ($class_performance as &$class) {
    $class['position'] = $position++;
}

// ============================================================================
// SUBJECT PERFORMANCE
// ============================================================================
$subject_performance = [];
if ($selected_session > 0 && $selected_term > 0) {
    $subject_query = "SELECT ss.subject,
                      ROUND(AVG(CAST(ist.score AS DECIMAL(5,2))), 1) as avg_score
                      FROM school_subject ss
                      LEFT JOIN input_score_class_teacher ist ON ss.id = ist.subject_id
                          AND ist.session_id = ? AND ist.term_id = ?
                          AND ist.create_by_userid = ?
                      WHERE ss.create_by_userid = ?
                      GROUP BY ss.id
                      ORDER BY avg_score DESC";
    $subject_performance = db_get_rows($subject_query, [$selected_session, $selected_term, $create_by_userid, $create_by_userid]);
}

// ============================================================================
// TOP 10 STUDENTS
// ============================================================================
$top_students = [];
if ($selected_session > 0 && $selected_term > 0) {
    $top_query = "SELECT 
                  ms.id, 
                  ms.first_name, 
                  ms.last_name,
                  ROUND(AVG(CAST(ist.score AS DECIMAL(5,2))), 1) as avg_score,
                  sc.name as class_name
                  FROM input_score_class_teacher ist
                  JOIN manage_student ms ON ist.student_id = ms.id
                  JOIN school_class sc ON ms.class = sc.id
                  WHERE ms.create_by_userid = ?
                  AND ist.session_id = ? 
                  AND ist.term_id = ?
                  AND ist.create_by_userid = ?
                  GROUP BY ms.id
                  ORDER BY avg_score DESC
                  LIMIT 10";
    $top_students = db_get_rows($top_query, [$create_by_userid, $selected_session, $selected_term, $create_by_userid]);
}

// ============================================================================
// CLASS LEADERBOARDS (TOP 5 STUDENTS IN EACH CLASS)
// ============================================================================
$class_leaderboards = [];
if ($selected_session > 0 && $selected_term > 0) {
    foreach ($classes as $classRow) {
        if ($selected_class > 0 && (int)$selected_class !== (int)$classRow['id']) {
            continue;
        }

        $leaderboardRows = db_get_rows(
            "SELECT ms.id, ms.first_name, ms.last_name, ms.student_id,
                    ROUND(AVG(CAST(ist.score AS DECIMAL(5,2))), 1) as avg_score
             FROM manage_student ms
             JOIN input_score_class_teacher ist ON ms.id = ist.student_id
             WHERE ms.class = ?
               AND ms.create_by_userid = ?
               AND ist.session_id = ?
               AND ist.term_id = ?
               AND ist.class_id = ?
               AND ist.create_by_userid = ?
             GROUP BY ms.id
             ORDER BY avg_score DESC, ms.first_name ASC
             LIMIT 5",
            [$classRow['id'], $create_by_userid, $selected_session, $selected_term, $classRow['id'], $create_by_userid]
        );

        $class_leaderboards[] = [
            'class_id' => $classRow['id'],
            'class_name' => $classRow['name'],
            'students' => $leaderboardRows,
            'count' => count($leaderboardRows),
        ];
    }
}

// ============================================================================
// STUDENTS PER CLASS
// ============================================================================
$students_per_class = db_get_rows(
    "SELECT sc.name as class_name, COUNT(ms.id) as student_count
     FROM school_class sc
     LEFT JOIN manage_student ms ON sc.id = ms.class AND ms.create_by_userid = ?
     WHERE sc.create_by_userid = ?
     GROUP BY sc.id
     ORDER BY student_count DESC",
    [$create_by_userid, $create_by_userid]
);

// ============================================================================
// GET CURRENT SESSION & TERM NAMES
// ============================================================================
$current_session_name = '';
foreach ($sessions as $s) {
    if ($s['id'] == $selected_session) {
        $current_session_name = $s['session'];
        break;
    }
}

$current_term_name = '';
foreach ($terms as $t) {
    if ($t['id'] == $selected_term) {
        $current_term_name = $t['term'];
        break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <title>Principal Dashboard - Best School Page</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }
        .header h1 { font-size: 20px; }
        .header h1 span { font-size: 12px; color: #aaa; }
        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
        }
        .sidebar {
            width: 250px;
            background: #1a1a2e;
            position: fixed;
            left: 0;
            top: 55px;
            bottom: 0;
            padding: 20px 0;
            overflow-y: auto;
        }
        .sidebar a {
            color: #aaa;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #16213e;
            color: white;
            border-left: 3px solid #667eea;
        }
        .main-content {
            margin-left: 250px;
            margin-top: 55px;
            padding: 20px;
        }
        .filter-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .filter-bar label { font-weight: bold; color: #333; }
        .filter-bar select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }
        .filter-bar button {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .kpi-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .kpi-card:hover { transform: translateY(-3px); }
        .kpi-icon { font-size: 40px; margin-bottom: 10px; }
        .kpi-value { font-size: 32px; font-weight: bold; color: #667eea; }
        .kpi-label { color: #666; font-size: 14px; margin-top: 5px; }
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .chart-card h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            color: #333;
        }
        .students-table {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .leaderboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .leaderboard-card {
            background: white;
            border-radius: 15px;
            padding: 18px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .leaderboard-card h3 {
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .leaderboard-card ol {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .leaderboard-card li {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .leaderboard-card li:last-child { border-bottom: none; }
        .leaderboard-name { font-weight: 600; color: #222; }
        .leaderboard-score { font-weight: 700; color: #667eea; }
        .insight-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .insight-card {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .insight-card h4 { margin-bottom: 8px; font-size: 14px; opacity: 0.85; }
        .insight-card .big { font-size: 28px; font-weight: 700; margin-bottom: 6px; }
        .insight-card .sub { font-size: 13px; color: rgba(255,255,255,0.75); }
        .students-table h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .students-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .students-table th, .students-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .students-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .rank-1 { background: #fff3cd; }
        .rank-2 { background: #f8f9fa; }
        .rank-3 { background: #e9ecef; }
        .progress-bar {
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            height: 20px;
            margin: 5px 0;
        }
        .progress-fill {
            background: #48bb78;
            height: 100%;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 5px;
            color: white;
            font-size: 11px;
        }
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .two-columns { grid-template-columns: 1fr; }
            .chart-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="fixed-left">
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="container" style="max-width:1400px; margin:0 auto; padding:20px;">
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <label>📅 Session:</label>
                    <select id="session_filter" onchange="applyFilters()">
                        <?php foreach($sessions as $session): ?>
                            <option value="<?php echo $session['id']; ?>" <?php echo $selected_session == $session['id'] ? 'selected' : ''; ?>>
                                <?php echo $session['session']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>📆 Term:</label>
                    <select id="term_filter" onchange="applyFilters()">
                        <?php foreach($terms as $term): ?>
                            <option value="<?php echo $term['id']; ?>" <?php echo $selected_term == $term['id'] ? 'selected' : ''; ?>>
                                <?php echo $term['term']; ?> Term
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>🏫 Class:</label>
                    <select id="class_filter" onchange="applyFilters()">
                        <option value="0">All Classes</option>
                        <?php foreach($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo $selected_class == $class['id'] ? 'selected' : ''; ?>>
                                <?php echo $class['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button onclick="applyFilters()">🔍 Apply Filters</button>
                </div>
                
                <!-- KPI Cards -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon">👨‍🎓</div>
                        <div class="kpi-value"><?php echo $students_data['total'] ?? 0; ?></div>
                        <div class="kpi-label">Total Students</div>
                        <small>👦 Male: <?php echo $students_data['male'] ?? 0; ?> | 👧 Female: <?php echo $students_data['female'] ?? 0; ?></small>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon">👨‍🏫</div>
                        <div class="kpi-value"><?php echo $staff_data['total'] ?? 0; ?></div>
                        <div class="kpi-label">Total Staff</div>
                        <small>👨 Male: <?php echo $staff_data['male'] ?? 0; ?> | 👩 Female: <?php echo $staff_data['female'] ?? 0; ?></small>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon">📚</div>
                        <div class="kpi-value"><?php echo $subjects_data ?? 0; ?></div>
                        <div class="kpi-label">Total Subjects</div>
                        <small>Active Subjects</small>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon">🏆</div>
                        <div class="kpi-value"><?php echo $current_session_name; ?></div>
                        <div class="kpi-label">Current Session</div>
                        <small><?php echo $current_term_name; ?> Term</small>
                    </div>
                </div>

                <?php
                    $bestClass = $class_performance[0] ?? [];
                    $worstClass = !empty($class_performance) ? $class_performance[count($class_performance) - 1] : [];
                    $classAvgValues = array_filter(array_map(function($row) {
                        return is_numeric($row['avg_score'] ?? null) ? (float)$row['avg_score'] : null;
                    }, $class_performance), function($value) {
                        return $value !== null;
                    });
                    $schoolAvg = !empty($classAvgValues) ? round(array_sum($classAvgValues) / count($classAvgValues), 1) : 0;
                    $topStudentCount = !empty($top_students) ? count($top_students) : 0;
                ?>

                <div class="insight-grid">
                    <div class="insight-card">
                        <h4>Top Performing Class</h4>
                        <div class="big"><?php echo htmlspecialchars($bestClass['class_name'] ?? 'N/A'); ?></div>
                        <div class="sub"><?php echo ($bestClass['avg_score'] ?? 0); ?>% average</div>
                    </div>
                    <div class="insight-card">
                        <h4>School Average</h4>
                        <div class="big"><?php echo $schoolAvg; ?>%</div>
                        <div class="sub">Across classes with score data</div>
                    </div>
                    <div class="insight-card">
                        <h4>Lowest Performing Class</h4>
                        <div class="big"><?php echo htmlspecialchars($worstClass['class_name'] ?? 'N/A'); ?></div>
                        <div class="sub"><?php echo ($worstClass['avg_score'] ?? 0); ?>% average</div>
                    </div>
                    <div class="insight-card">
                        <h4>Students Ranked</h4>
                        <div class="big"><?php echo $topStudentCount; ?></div>
                        <div class="sub">Top students in current session / term</div>
                    </div>
                </div>
                
                <!-- Class Performance Chart -->
                <div class="chart-grid">
                    <div class="chart-card">
                        <h3>📊 Class Performance - <?php echo $current_term_name; ?> Term <?php echo $current_session_name; ?></h3>
                        <canvas id="classPerformanceChart" height="120"></canvas>
                        <div style="margin-top: 15px;">
                            <?php foreach($class_performance as $class): ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($class['class_name']); ?></strong>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo min($class['avg_score'] ?? 0, 100); ?>%; background: <?php echo ($class['position'] ?? 1) == 1 ? '#48bb78' : (($class['position'] ?? 2) == 2 ? '#4299e1' : '#ed8936'); ?>;">
                                            <?php echo $class['avg_score'] ?? 0; ?>%
                                        </div>
                                    </div>
                                    <small>Position: <?php echo $class['position'] ?? 'N/A'; ?> | Students: <?php echo $class['student_count'] ?? 0; ?></small>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($class_performance)): ?>
                                <p style="color:#999; text-align:center;">No class performance data available. Please enter scores first.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                
                    <!-- Subject Performance Chart -->
                    <div class="chart-card">
                        <h3>📈 Subject Performance Analysis</h3>
                        <canvas id="subjectPerformanceChart" height="120"></canvas>
                        <?php if (empty($subject_performance)): ?>
                            <p style="color:#999; text-align:center; margin-top:10px;">No subject performance data available. Please enter scores first.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="students-table">
                    <h3>🏆 Executive Class Leaderboards - Top 5 Students in Each Class</h3>
                    <?php if (!empty($class_leaderboards)): ?>
                        <div class="leaderboard-grid">
                            <?php foreach ($class_leaderboards as $leaderboard): ?>
                                <div class="leaderboard-card">
                                    <h3><?php echo htmlspecialchars($leaderboard['class_name']); ?></h3>
                                    <?php if (!empty($leaderboard['students'])): ?>
                                        <ol>
                                            <?php foreach ($leaderboard['students'] as $index => $student): ?>
                                                <li class="<?php echo $index == 0 ? 'rank-1' : ''; ?>">
                                                    <div>
                                                        <div class="leaderboard-name"><?php echo ($index + 1) . '. ' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                                        <small><?php echo htmlspecialchars($student['student_id'] ?? ''); ?></small>
                                                    </div>
                                                    <div class="leaderboard-score"><?php echo $student['avg_score']; ?>%</div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    <?php else: ?>
                                        <p style="color:#999; text-align:center; margin:0;">No score data for this class yet.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color:#999; text-align:center;">No class leaderboards available. Please enter scores first.</p>
                    <?php endif; ?>
                </div>

                <div class="chart-card">
                    <h3>📋 Students Per Class</h3>
                    <canvas id="studentsPerClassChart" height="100"></canvas>
                </div>
                
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>

<script>
    // Apply filters - reload page with selected parameters
    function applyFilters() {
        const session = document.getElementById('session_filter').value;
        const term = document.getElementById('term_filter').value;
        const classId = document.getElementById('class_filter').value;
        window.location.href = `?session_id=${session}&term_id=${term}&class_id=${classId}`;
    }
    
    <?php if (!empty($class_performance)): ?>
    // Class Performance Chart
    const classLabels = <?php echo json_encode(array_column($class_performance, 'class_name')); ?>;
    const classScores = <?php echo json_encode(array_column($class_performance, 'avg_score')); ?>;
    
    new Chart(document.getElementById('classPerformanceChart'), {
        type: 'bar',
        data: {
            labels: classLabels,
            datasets: [{
                label: 'Average Score (%)',
                data: classScores,
                backgroundColor: ['#48bb78', '#4299e1', '#ed8936', '#ecc94b', '#e53e3e', '#667eea', '#764ba2', '#38b2ac'],
                borderRadius: 8,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Average Score (%)' }
                }
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: { callbacks: { label: function(context) { return context.raw + '%'; } } }
            }
        }
    });
    <?php endif; ?>
    
    <?php if (!empty($subject_performance)): ?>
    // Subject Performance Chart
    const subjectLabels = <?php echo json_encode(array_column($subject_performance, 'subject')); ?>;
    const subjectScores = <?php echo json_encode(array_column($subject_performance, 'avg_score')); ?>;
    
    new Chart(document.getElementById('subjectPerformanceChart'), {
        type: 'bar',
        data: {
            labels: subjectLabels,
            datasets: [{
                label: 'Average Score (%)',
                data: subjectScores,
                backgroundColor: '#667eea',
                borderRadius: 8,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Average Score (%)' }
                }
            },
            plugins: { legend: { position: 'top' } }
        }
    });
    <?php endif; ?>
    
    <?php if (!empty($students_per_class)): ?>
    // Students Per Class Chart
    const spcLabels = <?php echo json_encode(array_column($students_per_class, 'class_name')); ?>;
    const spcCounts = <?php echo json_encode(array_column($students_per_class, 'student_count')); ?>;
    
    new Chart(document.getElementById('studentsPerClassChart'), {
        type: 'bar',
        data: {
            labels: spcLabels,
            datasets: [{
                label: 'Number of Students',
                data: spcCounts,
                backgroundColor: '#38b2ac',
                borderRadius: 8,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Number of Students' } }
            }
        }
    });
    <?php endif; ?>
</script>

</body>
</html>