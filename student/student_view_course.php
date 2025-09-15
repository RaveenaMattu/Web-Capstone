<?php
session_start();
require_once('../database.php');

// Ensure user is logged in as Student
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'Student') {
    header('Location: ../login_form.php');
    exit();
}

$studentID = $_SESSION['userID'];
$fullName = $_SESSION['fullName'];
$role = $_SESSION['role'];

// Get courseID from query string
$courseID = isset($_GET['courseID']) ? intval($_GET['courseID']) : null;
if (!$courseID) {
    die("No course selected.");
}

// Fetch course info
$stmt = $db->prepare("SELECT c.*, i.firstName, i.lastName FROM courses c
                      JOIN instructors i ON c.instructorID = i.instructorID
                      WHERE c.courseID = :courseID");
$stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
$stmt->execute();
$course = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// Fetch enrolled classmates
$stmt = $db->prepare("SELECT s.firstName, s.lastName 
                      FROM course_enrollments ce
                      JOIN students s ON ce.studentID = s.studentID
                      WHERE ce.courseID = :courseID AND ce.status = 'enrolled'");
$stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
$stmt->execute();
$classmates = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// Fetch course materials
$stmt = $db->prepare("SELECT * FROM course_materials WHERE courseID = :courseID ORDER BY materialID ASC");
$stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
$stmt->execute();
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// Fetch quizzes
$stmt = $db->prepare("SELECT quizID, title FROM quizzes WHERE courseID = :courseID ORDER BY quizID ASC");
$stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
$stmt->execute();
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Course - Student</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/web-capstone/css/app.css">
<script src="/web-capstone/scripts/app.js" defer></script>
<style>
/* --- Layout Styles --- */
.main-grid { display: grid; grid-template-columns: 300px 1fr; gap: 30px; padding: 20px 300px; }
.left-column { display: grid; grid-template-rows: auto 1fr; gap: 20px; }
.course-card-compact { display: grid; grid-template-columns: 2fr 1fr; align-items: center; background: #fff; border-radius: 8px; padding: 10px 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); gap: 10px; }
.course-card-compact .course-info h3 { margin: 0; font-size: 1.1em; }
.course-card-compact .course-info span { font-size: 0.9em; color: #888; }
.course-card-compact .course-img img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
.nav-card { background: #fff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.nav-card ul { list-style: none; padding: 0; margin: 0; }
.nav-card li { margin: 5px 0; cursor: pointer; padding: 8px 12px; border-radius: 5px; transition: background 0.3s, color 0.3s; display: flex; align-items: center; justify-content: space-between; }
.nav-card li.active, .nav-card li:hover { background: #edf4fb; }
.material-item { padding-left: r5px; font-size: 0.95em; color: #555;}
#materialList li {margin-left: 15px;};
.right-content { display: grid; grid-template-rows: 1fr;}
.dynamic-content { display: grid; background: #fff; border-radius: 8px; padding:20px 50px 50px 50px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); min-height: 700px;}
.dynamic-content iframe { width: 100%; height: 600px; border: none; }
.dynamic-content table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
.dynamic-content th, .dynamic-content td { padding: 10px; text-align: left; border-radius: 8px;}
.dynamic-content th { background: #0053c8; color: #fff; }
.dynamic-content tr { background: #edf4fb;}
</style>
</head>
<body data-role="<?= htmlspecialchars($role); ?>">
<?php include('student_header.php'); ?>

<div class="main-grid">
  <!-- Left Column -->
  <div class="left-column">
    <!-- Course Card -->
    <div class="course-card-compact">
      <div class="course-info">
        <h3><?= htmlspecialchars($course['courseName']); ?></h3>
        <span><?= htmlspecialchars($course['courseCode']); ?></span>
        <span style="display:block; font-size:0.85em; color:#555;">Instructor: <?= htmlspecialchars($course['firstName'].' '.$course['lastName']); ?></span>
      </div>
      <div class="course-img">
        <img src="<?= !empty($course['imageName']) ? '/web-capstone/images/'.htmlspecialchars($course['imageName']) : '/web-capstone/images/book_placeholder.png'; ?>" alt="Course Image">
      </div>
    </div>

    <!-- Nav Card -->
    <div class="nav-card">
      <ul>
        <li class="active" data-target="content">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span>Content</span>
            <button id="toggleMaterialList" style="cursor:pointer; border:none; background:none; font-size:1em; margin: 0; padding: 0; margin-left: 150px;">
              <i class="fas fa-chevron-down"></i>
            </button>
          </div>
        </li>
        <div id="materialListContainer" style="overflow:hidden; max-height:0; transition:max-height 0.3s ease; margin-bottom:10px;">
          <ul id="materialList"></ul>
        </div>
        <li data-target="classmates">Classlist</li>
        <li data-target="quizzes">Quizzes</li>
        <li data-target="textbook">Textbook</li>
      </ul>
    </div>
  </div>

  <!-- Right Content -->
  <div class="right-content">
    <div class="dynamic-content" id="dynamicContent">
      <h2 style="text-align:center; font-weight: 500;">Course Overview</h2>
      <?php if(!empty($course['overview_pdf_path'])): ?>
        <iframe src="<?= htmlspecialchars($course['overview_pdf_path']); ?>" title="Course Overview"></iframe>
      <?php else: ?>
        <p style="text-align:center;">No course overview uploaded yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer class="footer">
  © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
</footer>

<script type="module">
   import {loadStudentQuizzes} from './load_quiz.js';

const studentID = <?= $studentID ? intval($studentID) : 'null'; ?>;
// JSON data
const materialsData = <?= json_encode($materials); ?>;
const classmatesData = <?= json_encode($classmates); ?>;
const quizzesData = <?= json_encode($quizzes); ?>;
const dynamicContent = document.getElementById('dynamicContent');

// Material List
const materialListContainer = document.getElementById('materialListContainer');
const materialList = document.getElementById('materialList');
const toggleBtn = document.getElementById('toggleMaterialList');

// Populate Material List
materialsData.forEach(m => {
  const li = document.createElement('li');
  li.className = "material-item";
  li.textContent = m.title;
  li.style.cursor = "pointer";

  li.addEventListener('click', () => {
    const ext = m.file_path.split('.').pop().toLowerCase();
    let content = `<h2 style="text-align:center; font-weight: 500;">${m.title}</h2>`;
    if (['pdf','ppt','pptx'].includes(ext)) {
      const gviewURL = ext==='pdf' ? m.file_path : "https://docs.google.com/gview?url="+encodeURIComponent(window.location.origin + m.file_path)+"&embedded=true";
      content += `<iframe src="${gviewURL}"></iframe>`;
    } else if (['jpg','jpeg','png','gif'].includes(ext)) {
      content += `<img src="${m.file_path}" style="max-width:100%;">`;
    } else {
      content += `<a href="${m.file_path}" target="_blank">Download File</a>`;
    }
    dynamicContent.innerHTML = content;
  });

  materialList.appendChild(li);
});

// Toggle Material List
toggleBtn.addEventListener('click', () => {
  const isOpen = materialListContainer.style.maxHeight !== "0px";
  if (isOpen) {
    materialListContainer.style.maxHeight = "0px";
    toggleBtn.innerHTML = '<i class="fas fa-chevron-down"></i>';
  } else {
    materialListContainer.style.maxHeight = materialList.scrollHeight + "px";
    toggleBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
  }
});

// Nav Switching
document.querySelectorAll('.nav-card li').forEach(item => {
  item.addEventListener('click', () => {
    document.querySelectorAll('.nav-card li').forEach(i => i.classList.remove('active'));
    item.classList.add('active');

    const target = item.dataset.target;

    if(target === 'content') {
      dynamicContent.innerHTML = `<?php if(!empty($course['overview_pdf_path'])): ?><iframe src="<?= htmlspecialchars($course['overview_pdf_path']); ?>" title="Course Overview"></iframe><?php else: ?><p style="text-align:center;">No course overview uploaded yet.</p><?php endif; ?>`;
    }
    else if (target === 'classmates') {
      let tableHTML = `<div style="width:100%;"><h2 style="font-weight: 500; color: #000; text-align: center; padding: 20px 0;"> Enrolled Students (Total: ${classmatesData.length})</h2>
      <table><thead><tr><th>Student Name</th></tr></thead><tbody>`;
      classmatesData.forEach(s => tableHTML += `<tr><td>${s.firstName} ${s.lastName}</td></tr>`);
      tableHTML += '</tbody></table></div>';
      dynamicContent.innerHTML = classmatesData.length > 0 ? tableHTML : '<p>No students enrolled yet for this course.</p>';

    }
    else if(target === 'quizzes') {
      loadStudentQuizzes(studentID, <?= $courseID?>);
    }
    else if(target === 'textbook') {
      if('<?= $course['textbook_pdf_path'] ?>'){
        dynamicContent.innerHTML = `<h2 style="text-align:center; font-weight: 500;">Textbook</h2><iframe src="<?= $course['textbook_pdf_path'] ?>" title="Textbook"></iframe>`;
      } else {
        dynamicContent.innerHTML = `<p style="text-align:center;">No textbook uploaded yet.</p>`;
      }
    }
  });
});
</script>
</body>
</html>
