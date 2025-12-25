<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "for_activity";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    grade VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql);

$message = '';
$message_type = '';
$view_student = null;

// Handle form submission - ADD STUDENT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    
    $sql = "INSERT INTO students (student_id, name, age, grade) 
            VALUES ('$student_id', '$name', '$age', '$grade')";
    
    if (mysqli_query($conn, $sql)) {
        $message = "🎓 Student information saved successfully!";
        $message_type = 'success';
    } else {
        $message = "❌ Error: " . mysqli_error($conn);
        $message_type = 'error';
    }
}

// Handle VIEW request
if (isset($_GET['view_id'])) {
    $view_id = mysqli_real_escape_string($conn, $_GET['view_id']);
    
    $sql = "SELECT * FROM students WHERE id = '$view_id'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $view_student = mysqli_fetch_assoc($result);
    }
}

// Handle DELETE request
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    $sql = "DELETE FROM students WHERE id = '$delete_id'";
    
    if (mysqli_query($conn, $sql)) {
        $message = "🗑️ Student record deleted successfully!";
        $message_type = 'success';
    } else {
        $message = "❌ Error deleting record: " . mysqli_error($conn);
        $message_type = 'error';
    }
    
    // Redirect to remove delete_id from URL
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

// Fetch all students
$result = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC");

// Get statistics
$count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM students");
$total = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;

$avg_result = mysqli_query($conn, "SELECT AVG(age) as avg_age FROM students");
$avg_age = $avg_result ? mysqli_fetch_assoc($avg_result)['avg_age'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --antique: #faebd7;
            --antique-dark: #e8d8c3;
            --antique-darker: #d4c4a8;
            --bronze: #cd7f32;
            --copper: #b87333;
            --sepia: #704214;
            --ivory: #fffff0;
            --parchment: #f5f5dc;
            --text-dark: #5d4037;
            --text-light: #8d6e63;
            --danger: #c62828;
            --success: #2e7d32;
            --info: #1565c0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: linear-gradient(135deg, var(--antique) 0%, var(--parchment) 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--text-dark);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Header */
        .vintage-header {
            text-align: center;
            margin-bottom: 50px;
            padding: 40px 30px;
            background: var(--ivory);
            border-radius: 15px;
            border: 2px solid var(--bronze);
            position: relative;
            box-shadow: 0 10px 30px rgba(93, 64, 55, 0.1);
        }
        
        .vintage-title {
            font-size: 2.5rem;
            color: var(--sepia);
            margin-bottom: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            position: relative;
            display: inline-block;
        }
        
        .vintage-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 25%;
            width: 50%;
            height: 3px;
            background: linear-gradient(to right, transparent, var(--bronze), transparent);
        }
        
        /* Messages */
        .message-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 5px solid;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .success-msg {
            border-left-color: var(--success);
            background: #e8f5e9;
            color: #1b5e20;
        }
        
        .error-msg {
            border-left-color: var(--danger);
            background: #ffebee;
            color: #b71c1c;
        }
        
        .info-msg {
            border-left-color: var(--info);
            background: #e3f2fd;
            color: #0d47a1;
        }
        
        /* Main Layout */
        .main-layout {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        /* Left Panel - Form & Stats */
        .left-panel {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        /* Form */
        .form-container {
            background: var(--ivory);
            border-radius: 15px;
            padding: 25px;
            border: 2px solid var(--bronze);
            box-shadow: 0 8px 25px rgba(93, 64, 55, 0.15);
            position: relative;
        }
        
        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, var(--bronze), var(--copper), var(--bronze));
        }
        
        .form-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            color: var(--sepia);
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--sepia);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .vintage-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--antique-darker);
            border-radius: 8px;
            font-size: 16px;
            background: var(--antique);
            font-family: 'Georgia', serif;
            color: var(--text-dark);
        }
        
        .vintage-input:focus {
            border-color: var(--bronze);
            outline: none;
            box-shadow: 0 0 0 3px rgba(205, 127, 50, 0.2);
        }
        
        .vintage-btn {
            background: linear-gradient(to bottom, var(--bronze), var(--sepia));
            color: var(--ivory);
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 1px;
            font-family: 'Georgia', serif;
            border: 1px solid var(--copper);
        }
        
        .vintage-btn:hover {
            background: linear-gradient(to bottom, var(--copper), var(--sepia));
            transform: translateY(-2px);
        }
        
        /* Statistics */
        .stats-container {
            background: var(--ivory);
            border-radius: 15px;
            padding: 25px;
            border: 2px solid var(--bronze);
            box-shadow: 0 8px 25px rgba(93, 64, 55, 0.15);
        }
        
        .stats-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            color: var(--sepia);
            font-size: 1.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .stat-card {
            text-align: center;
            padding: 15px;
            background: var(--antique);
            border-radius: 10px;
            border: 1px solid var(--antique-darker);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--bronze);
            line-height: 1;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Students List */
        .students-container {
            background: var(--ivory);
            border-radius: 15px;
            padding: 25px;
            border: 2px solid var(--bronze);
            box-shadow: 0 8px 25px rgba(93, 64, 55, 0.15);
        }
        
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .list-title {
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--sepia);
            font-size: 1.5rem;
        }
        
        .total-badge {
            background: var(--bronze);
            color: var(--ivory);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        /* Student Table */
        .student-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .student-table th {
            background: var(--antique);
            color: var(--sepia);
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--bronze);
        }
        
        .student-table td {
            padding: 12px;
            border-bottom: 1px solid var(--antique-dark);
        }
        
        .student-table tr:hover {
            background: var(--antique);
        }
        
        .student-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Grade Badges */
        .grade-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .first-year { background: #e3f2fd; color: #1565c0; }
        .second-year { background: #f3e5f5; color: #7b1fa2; }
        .third-year { background: #e8f5e9; color: #2e7d32; }
        .fourth-year { background: #fff3e0; color: #ef6c00; }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .view-btn {
            background: var(--info);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .view-btn:hover {
            background: #0d47a1;
            transform: scale(1.05);
        }
        
        .delete-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .delete-btn:hover {
            background: #b71c1c;
            transform: scale(1.05);
        }
        
        /* View Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .view-modal {
            background: var(--ivory);
            border-radius: 15px;
            border: 2px solid var(--bronze);
            max-width: 500px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }
        
        .view-header {
            background: var(--bronze);
            color: var(--ivory);
            padding: 20px;
            border-radius: 13px 13px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .view-header h3 {
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .close-btn {
            background: transparent;
            border: none;
            color: var(--ivory);
            font-size: 1.3rem;
            cursor: pointer;
            padding: 5px;
        }
        
        .view-body {
            padding: 20px;
        }
        
        .student-profile {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--antique-darker);
        }
        
        .profile-icon {
            font-size: 3.5rem;
            color: var(--bronze);
            background: var(--antique);
            padding: 15px;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .profile-info h4 {
            font-size: 1.5rem;
            color: var(--sepia);
            margin-bottom: 8px;
        }
        
        .profile-id {
            background: var(--antique);
            color: var(--text-dark);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 8px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .detail-item {
            background: var(--antique);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--antique-darker);
        }
        
        .detail-label {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .detail-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .view-footer {
            padding: 15px 20px;
            background: var(--antique);
            border-radius: 0 0 13px 13px;
            text-align: center;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }
        
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--antique-darker);
        }
        
        /* Footer */
        .vintage-footer {
            text-align: center;
            color: var(--text-light);
            padding: 20px;
            font-size: 0.85rem;
            background: var(--ivory);
            border-radius: 10px;
            border: 1px solid var(--bronze);
            margin-top: 30px;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
            
            .left-panel {
                grid-template-columns: repeat(2, 1fr);
                display: grid;
            }
        }
        
        @media (max-width: 768px) {
            .vintage-title {
                font-size: 2rem;
            }
            
            .student-table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .left-panel {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .student-profile {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="vintage-header">
            <h1 class="vintage-title">
                <i class="fas fa-university"></i>
                Student Information
            </h1>
            <p>Group 6 Activity • Create, View, Delete Operations</p>
        </div>
        
        <!-- Messages -->
        <?php if (!empty($message)): ?>
            <div class="message-box <?php echo $message_type == 'success' ? 'success-msg' : ($message_type == 'info' ? 'info-msg' : 'error-msg'); ?>">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : ($message_type == 'info' ? 'info-circle' : 'exclamation-circle'); ?> fa-lg"></i>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Main Content -->
        <div class="main-layout">
            <!-- Left Panel -->
            <div class="left-panel">
                <!-- Form -->
                <div class="form-container">
                    <h2 class="form-title">
                        <i class="fas fa-user-plus"></i>
                        Add New Student
                    </h2>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label" for="student_id">
                                <i class="fas fa-id-card"></i>
                                Student ID
                            </label>
                            <input type="text" class="vintage-input" id="student_id" name="student_id" 
                                   placeholder="Enter student ID" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="name">
                                <i class="fas fa-user"></i>
                                Full Name
                            </label>
                            <input type="text" class="vintage-input" id="name" name="name" 
                                   placeholder="Enter full name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="age">
                                <i class="fas fa-birthday-cake"></i>
                                Age
                            </label>
                            <input type="number" class="vintage-input" id="age" name="age" 
                                   min="10" max="50" placeholder="Enter age" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="grade">
                                <i class="fas fa-graduation-cap"></i>
                                Grade Level
                            </label>
                            <select class="vintage-input" id="grade" name="grade" required style="appearance: none; padding: 12px 15px;">
                                <option value="">Select Grade Level</option>
                                <option value="First Year College">First Year College</option>
                                <option value="Second Year College">Second Year College</option>
                                <option value="Third Year College">Third Year College</option>
                                <option value="Fourth Year College">Fourth Year College</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="add_student" class="vintage-btn">
                            <i class="fas fa-plus-circle"></i>
                            Add Student Record
                        </button>
                    </form>
                </div>
                
                <!-- Statistics -->
                <div class="stats-container">
                    <h2 class="stats-title">
                        <i class="fas fa-chart-bar"></i>
                        System Statistics
                    </h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $total; ?></div>
                            <div class="stat-label">Total Students</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $avg_age ? round($avg_age, 1) : '0'; ?></div>
                            <div class="stat-label">Average Age</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Students List -->
            <div class="students-container">
                <div class="list-header">
                    <h2 class="list-title">
                        <i class="fas fa-users"></i>
                        Student Records
                    </h2>
                    <div class="total-badge">
                        Total: <?php echo $total; ?>
                    </div>
                </div>
                
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <table class="student-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['age']); ?></td>
                                <td>
                                    <?php 
                                    $grade_class = strtolower(str_replace(' ', '-', $row['grade']));
                                    ?>
                                    <span class="grade-badge <?php echo $grade_class; ?>">
                                        <?php echo htmlspecialchars($row['grade']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?view_id=<?php echo $row['id']; ?>" class="view-btn">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="?delete_id=<?php echo $row['id']; ?>" 
                                           class="delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($row['name']); ?>?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-users-slash"></i>
                        </div>
                        <h3>No Student Records Found</h3>
                        <p>Add your first student using the form on the left</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="vintage-footer">
            <p><i class="fas fa-database"></i> Database: <strong>for_activity</strong> • Students Table</p>
            <p>© 2024 Student Information System • Activity Date: 12/10/2019 • Group 6</p>
        </div>
    </div>
    
    <!-- View Modal -->
    <?php if ($view_student): ?>
    <div class="modal-overlay" id="viewModal" style="display: flex;">
        <div class="view-modal">
            <div class="view-header">
                <h3><i class="fas fa-user-graduate"></i> Student Details</h3>
                <button class="close-btn" onclick="closeViewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="view-body">
                <div class="student-profile">
                    <div class="profile-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="profile-info">
                        <h4><?php echo htmlspecialchars($view_student['name']); ?></h4>
                        <div class="profile-id">
                            <i class="fas fa-id-card"></i> ID: <?php echo htmlspecialchars($view_student['student_id']); ?>
                        </div>
                        <p>Complete student information</p>
                    </div>
                </div>
                
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-id-badge"></i> Student ID
                        </div>
                        <div class="detail-value"><?php echo htmlspecialchars($view_student['student_id']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-signature"></i> Full Name
                        </div>
                        <div class="detail-value"><?php echo htmlspecialchars($view_student['name']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-calendar-alt"></i> Age
                        </div>
                        <div class="detail-value"><?php echo htmlspecialchars($view_student['age']); ?> years old</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-graduation-cap"></i> Grade Level
                        </div>
                        <div class="detail-value">
                            <span class="grade-badge <?php echo strtolower(str_replace(' ', '-', $view_student['grade'])); ?>">
                                <?php echo htmlspecialchars($view_student['grade']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="view-footer">
                <button class="vintage-btn" onclick="closeViewModal()" style="max-width: 200px; margin: 0 auto;">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // View Modal functions
        function closeViewModal() {
            // Remove view_id from URL and reload
            window.location.href = window.location.pathname;
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('viewModal');
            if (viewModal && event.target == viewModal) {
                closeViewModal();
            }
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeViewModal();
            }
        });
        
        // Form validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const age = document.getElementById('age').value;
                if (age < 10 || age > 50) {
                    alert('Please enter a valid age between 10 and 50.');
                    e.preventDefault();
                }
            });
        }
        
        // Add animation to table rows
        const tableRows = document.querySelectorAll('.student-table tbody tr');
        tableRows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.1}s`;
            row.style.animation = 'fadeIn 0.5s ease forwards';
            row.style.opacity = '0';
        });
        
        // Add CSS for fadeIn animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>