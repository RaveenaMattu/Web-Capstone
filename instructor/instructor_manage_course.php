<?php
// instructor_manage_course.php
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

session_start();
require_once('../database.php');

// Ensure user is logged in as Instructor
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'Instructor') {
    header('Location: ../login.php');
    exit();
}

$instructorID = $_SESSION['userID'];
$role = $_SESSION['role'];

// Fetch all courses assigned to this instructor
$stmt = $db->prepare('SELECT * FROM courses WHERE instructorID = :id');
$stmt->bindValue(':id', $instructorID, PDO::PARAM_INT);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// Get selected course
$course = null;
$courseID = null;
if (isset($_GET['courseID'])) {
    $courseID = intval($_GET['courseID']);
    $stmt = $db->prepare('SELECT * FROM courses WHERE courseID = :courseID AND instructorID = :instructorID');
    $stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
    $stmt->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
}

// Fetch student list for all courses of this instructor
$queryStudents = "
    SELECT s.studentID, s.firstName, s.lastName, c.courseName, c.courseID
    FROM course_enrollments ce
    JOIN students s ON ce.studentID = s.studentID
    JOIN courses c ON ce.courseID = c.courseID
    WHERE c.instructorID = :instructorID AND ce.status = 'enrolled'
    ORDER BY c.courseName, s.firstName
";
$statement = $db->prepare($queryStudents);
$statement->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
$statement->execute();
$students = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();

// Fetch materials for the selected course
$materials = [];
if ($courseID) {
    $stmt = $db->prepare("SELECT * FROM course_materials WHERE courseID = :courseID ORDER BY uploaded_at DESC");
    $stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
    $stmt->execute();
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Course</title>
<script src="/web-capstone/scripts/app.js" defer></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/web-capstone/css/app.css">
<style>
.main-grid { display: grid; grid-template-columns: 300px 1fr; gap: 30px; padding: 20px 300px; }
.left-column { display: grid; grid-template-rows: auto 1fr; gap: 20px; }
.course-card-compact { display: grid; grid-template-columns: 2fr 1fr; align-items: center; background: #fff; border-radius: 8px; padding: 10px 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); gap: 10px; }
.course-card-compact .course-info h3 { margin: 0; font-size: 1.1em; }
.course-card-compact .course-info span { font-size: 0.9em; color: #888; }
.course-card-compact .course-img img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
.nav-card { background: #fff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.nav-card ul { list-style: none; padding: 0; margin: 0; }
.nav-card li { margin: 10px 0; cursor: pointer; padding: 8px 12px; border-radius: 5px; transition: background 0.3s, color 0.3s; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;}
.nav-card li.active, .nav-card li:hover { background: #edf4fb; }
.nav-card li > i {height: 40px; width: 40px;}
.nav-card li > button { background: none; padding: 0; margin: 0;}
.nav-card li > button:hover { background: #edf4fb; }
.material-item { padding-left: 15px; font-size: 0.95em; cursor: pointer; color: #444; }
.material-item:hover { text-decoration: underline; }
.right-content { display: grid; grid-template-rows: 1fr; }
.dynamic-content { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); min-height: 300px; }
#addUpdateForm { display: grid; align-items: center; gap: 10px; grid-template-columns: 200px 1fr 1fr; padding: 0; box-shadow: none; }
#addUpdateForm input[type="text"], #addUpdateForm input[type="file"] { padding: 10px; font-size: 1em; }
#addUpdateForm button { padding: 10px 20px; font-size: 1em; background-color: #2563eb; color: #fff; border: none; border-radius: 8px; cursor: pointer; white-space: nowrap; }
#addUpdateForm button:hover { background-color: #1d4ed8; }
.dynamic-content table { width: 100%; border-collapse: collapse; }
.dynamic-content th, .dynamic-content td { padding: 10px; text-align: left; }
.dynamic-content th { background: #2563eb; color: #fff; }
</style>
</head>
<body data-role="<?= htmlspecialchars($role); ?>">
<?php include('instructor_header.php'); ?>

<div class="main-grid">
    <!-- Left column -->
    <div class="left-column">
        <?php if($course): ?>
        <div class="course-card-compact">
            <div class="course-info">
                <h3><?= htmlspecialchars($course['courseName']); ?></h3>
                <span><?= htmlspecialchars($course['courseCode']); ?></span>
            </div>
            <div class="course-img">
                <img src="<?= !empty($course['imageName']) ? '/web-capstone/images/' . htmlspecialchars($course['imageName']) : '/web-capstone/images/placeholder.jpg'; ?>" alt="Course Image">
            </div>
        </div>
        <?php endif; ?>
        <div class="nav-card">
            <ul>
                <li class="active" data-target="content">Content</li>
                <ul id="materialList" style="padding-left:10px;"></ul>
                <li data-target="student-list">Student List</li>
                <li data-target="quizzes">Quizzes</li>
                <li data-target="textbook">Textbook</li>
            </ul>
        </div>
    </div>

    <!-- Right content -->
    <div class="right-content">
        <div class="dynamic-content" id="dynamicContent">
            <form action="add_material.php" method="POST" enctype="multipart/form-data" id="addUpdateForm">
                <input type="hidden" name="courseID" value="<?= $courseID; ?>">
                <input type="text" name="title" placeholder="Material Title" required>
                <input type="file" name="materialFile" required>
                <button type="submit">Add Material</button>
            </form>
            <div id="materialPreview" style="margin-top:20px;"></div>
        </div>
    </div>
</div>

<footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
</footer>

<script>
const navItems = document.querySelectorAll('.nav-card li');
const dynamicContent = document.getElementById('dynamicContent');

const studentsData = <?= json_encode($students); ?>;
// Assuming materialsData is your PHP array passed to JS
const materialsData = <?= json_encode($materials); ?>;
const selectedCourseID = <?= $courseID ? intval($courseID) : 'null' ?>;

const materialList = document.getElementById('materialList');
const materialPreview = document.getElementById('materialPreview');

materialsData.forEach(material => {
    const li = document.createElement('li');
    li.textContent = material.title;
    li.className = 'material-item';
    const editBtn = document.createElement('button');
    editBtn.innerHTML = '<i class="fas fa-edit"></i>';
    editBtn.title = 'Edit';
    editBtn.style.marginRight = '5px';
    editBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent triggering li click
        const newTitle = prompt('Update material title:', titleSpan.textContent);
        if(newTitle) titleSpan.textContent = newTitle;

        // Optional: make AJAX request to update in DB
        // fetch(`update_material.php?id=${material.id}`, {...})
    });

    // Delete button
    const deleteBtn = document.createElement('button');
    deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
    deleteBtn.title = 'Delete';
    deleteBtn.style.marginRight = '5px';
    deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent triggering li click
        if(confirm('Are you sure you want to delete this material?')) {
            li.remove(); // Remove from UI

            // Optional: make AJAX request to delete from DB
            // fetch(`delete_material.php?id=${material.id}`, {...})
        }
    });

    const toggleBtn = document.createElement('button');
    toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
    toggleBtn.title = 'Hide/Show';
    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if(materialPreview.innerHTML.includes(material.file_path)) {
            materialPreview.innerHTML = ''; // Hide preview
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
        } else {
            toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
        }
    });

    li.addEventListener('click', () => {
        const filePath = material.file_path; 
        console.log(filePath);
        const ext = filePath.split('.').pop().toLowerCase();
         console.log(ext);
        const fileURL = encodeURIComponent('http://localhost' + filePath); // encode URL
        console.log(fileURL);

        if (['ppt', 'pptx'].includes(ext)) {
            materialPreview.innerHTML = `
                <iframe src="https://docs.google.com/gview?url=${fileURL}&embedded=true"
                        style="width:100%; height:600px;" frameborder="0"></iframe>
            `;
        } else if (['pdf'].includes(ext)) {
            materialPreview.innerHTML = `<iframe src="${filePath}" style="width:100%; height:600px;"></iframe>`;
        } else if (['jpg','jpeg','png','gif'].includes(ext)) {
            materialPreview.innerHTML = `<img src="${filePath}" style="max-width:100%;">`;
        } else {
            materialPreview.innerHTML = `<a href="${filePath}" target="_blank">Download</a>`;
        }
    });
    li.appendChild(editBtn);
    li.appendChild(deleteBtn);
    li.appendChild(toggleBtn);
    materialList.appendChild(li);
});


// Dynamic content switching
navItems.forEach(item => {
    item.addEventListener('click', () => {
        navItems.forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        const target = item.dataset.target;

        if(target === 'content') {
            dynamicContent.innerHTML = `
                <form action="add_material.php" method="POST" enctype="multipart/form-data" id="addUpdateForm">
                    <input type="hidden" name="courseID" value="${selectedCourseID}">
                    <input type="text" name="title" placeholder="Material Title" required>
                    <input type="file" name="materialFile" required>
                    <button type="submit">Add Material</button>
                </form>
                <div id="materialPreview" style="margin-top:20px;"></div>
            `;
        } else if (target === 'student-list') {
            const filtered = studentsData.filter(s => parseInt(s.courseID) === parseInt(selectedCourseID));
            if(filtered.length > 0){
                let tableHTML = `<h3>Enrolled Students (Total: ${filtered.length})</h3><table><thead><tr><th>Student Name</th></tr></thead><tbody>`;
                filtered.forEach(s => { tableHTML += `<tr><td>${s.firstName} ${s.lastName}</td></tr>`; });
                tableHTML += `</tbody></table>`;
                dynamicContent.innerHTML = tableHTML;
            } else dynamicContent.innerHTML = '<p>No students enrolled yet for this course.</p>';
        } else if(target === 'quizzes') dynamicContent.innerHTML = `<p>Quizzes management will appear here.</p>`;
        else if(target === 'textbook') dynamicContent.innerHTML = `<p>Textbook or additional resources will appear here.</p>`;
    });
});
</script>
</body>
</html>
