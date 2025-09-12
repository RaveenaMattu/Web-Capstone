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
    $stmt = $db->prepare("SELECT * FROM course_materials WHERE courseID = :courseID ORDER BY materialID ASC");
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/web-capstone/css/app.css">
<script src="/web-capstone/scripts/app.js" defer></script>
<style>
/* --- Styles --- */
.main-grid { display: grid; grid-template-columns: 300px 1fr; gap: 30px; padding: 20px 300px; }
.left-column { display: grid; grid-template-rows: auto 1fr; gap: 20px; }
.course-card-compact { display: grid; grid-template-columns: 2fr 1fr; align-items: center; background: #fff; border-radius: 8px; padding: 10px 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); gap: 10px; }
.course-card-compact .course-info h3 { margin: 0; font-size: 1.1em; }
.course-card-compact .course-info span { font-size: 0.9em; color: #888; }
.course-card-compact .course-img img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
.nav-card { background: #fff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.nav-card ul { list-style: none; padding: 0; margin: 0; }
.nav-card li { margin: 10px 0; cursor: pointer; padding: 8px 12px; border-radius: 5px; transition: background 0.3s, color 0.3s; display: flex; align-items: center; justify-content: space-between; }
.nav-card li button{ background: none; margin: 0; padding: 0 10px;}
.nav-card li.active, .nav-card li:hover { background: #edf4fb; }
.material-item { padding-left: 15px; font-size: 0.95em; cursor: pointer; color: #444; }
.material-item:hover { text-decoration: underline; }
.right-content { display: grid; grid-template-rows: 1fr;}
.dynamic-content { display: grid; background: #fff; border-radius: 8px; padding: 20px 50px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); min-height: 300px;}
.dynamic-content table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
.dynamic-content th, .dynamic-content td { padding: 10px; text-align: left; border-radius: 8px;}
.dynamic-content th { background: #0053c8; color: #fff; }
.dynamic-content tr { background: #edf4fb;}
#forms tr {background: none;}
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
        <li class="active" data-target="content">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span>Content</span>
            <button id="toggleMaterialList" style="cursor:pointer; border:none; background:none; font-size:1em; margin-left: 145px;">
              <i class="fas fa-chevron-down"></i>
            </button>
          </div>
        </li>
        <div id="materialListContainer" style="overflow:hidden; max-height:0; transition:max-height 0.3s ease; margin-bottom:10px;">
            <ul id="materialList"></ul>
        </div>
        <li data-target="student-list">Student List</li>
        <li data-target="quizzes">Quizzes</li>
        <li data-target="textbook">Textbook</li>
        <li data-target="upload">Uploads</li>
      </ul>
    </div>
  </div>

  <!-- Right content -->
  <div class="right-content">
    <div class="dynamic-content" id="dynamicContent">
      <!-- Overview shown by default -->
      <div id="courseOverview" style="margin-top: 20px; border-radius: 8px;">
        <h2 style="font-weight: 500; text-align: center;">Course Overview</h2>
        <?php if (!empty($course['overview_pdf_path'])): ?>
          <iframe src="<?= htmlspecialchars($course['overview_pdf_path']); ?>" style="width:100%; height:500px; border:none;" title="Course Overview"></iframe>
        <?php else: ?>
          <p>No course overview uploaded yet.</p>
        <?php endif; ?>
      </div>
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
const materialsData = <?= json_encode($materials); ?>;
const selectedCourseID = <?= $courseID ? intval($courseID) : 'null'; ?>;
const toggleBtn = document.getElementById('toggleMaterialList');
const materialListContainer = document.getElementById('materialListContainer');
const materialList = document.getElementById('materialList');

// Populate material list
materialsData.forEach(material => {
  const li = document.createElement('li');
  li.className = 'material-item';
  li.style.display = 'flex';
  li.style.alignItems = 'center';

  const titleSpan = document.createElement('span');
  titleSpan.textContent = material.title;
  li.appendChild(titleSpan);

  const iconContainer = document.createElement('div');
  iconContainer.style.marginLeft = 'auto';
  iconContainer.style.display = 'flex';
  iconContainer.style.gap = '5px';

  const deleteBtn = document.createElement('button');
  deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
  deleteBtn.title = 'Delete';
  deleteBtn.addEventListener('click', e => {
    e.stopPropagation();
    if (confirm('Delete this material?')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = 'instructor_delete_course_material.php';
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'materialID';
      input.value = material.materialID;
      form.appendChild(input);
      document.body.appendChild(form);
      form.submit();
    }
  });

  iconContainer.appendChild(deleteBtn);
  li.appendChild(iconContainer);

  // Material preview
  li.addEventListener('click', () => {
    const filePath = material.file_path;
    const ext = filePath.split('.').pop().toLowerCase();
    let content = ``;

    if (['ppt','pptx'].includes(ext)) {
      const gviewURL = "https://docs.google.com/gview?url=" + encodeURIComponent("http://localhost" + filePath) + "&embedded=true";
      content += `<iframe src="${gviewURL}" style="width:100%;height:600px;" frameborder="0"></iframe>`;
    } else if (['pdf'].includes(ext)) {
      content += `<iframe src="${filePath}" style="width:100%;height:600px;"></iframe>`;
    } else if (['jpg','jpeg','png','gif'].includes(ext)) {
      content += `<img src="${filePath}" style="max-width:100%;">`;
    } else {
      content += `<a href="${filePath}" target="_blank">Download</a>`;
    }

    dynamicContent.innerHTML = content;

    document.getElementById('closePreview').addEventListener('click', () => {
      // Back to Overview
      navItems.forEach(i => { if (i.dataset.target === 'content') i.click(); });
    });
  });

  materialList.appendChild(li);
});

// Toggle material list
toggleBtn.addEventListener('click', e => {
  e.stopPropagation();
  if (materialListContainer.style.maxHeight === '0px' || materialListContainer.style.maxHeight === '') {
    materialListContainer.style.maxHeight = materialList.scrollHeight + 20 + 'px';
    toggleBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
  } else {
    materialListContainer.style.maxHeight = '0';
    toggleBtn.innerHTML = '<i class="fas fa-chevron-down"></i>';
  }
});

// Nav switching
navItems.forEach(item => {
  item.addEventListener('click', () => {
    navItems.forEach(i => i.classList.remove('active'));
    item.classList.add('active');
    const target = item.dataset.target;

    if (target === 'content') {
      // Show Overview
      const overviewPath = <?= json_encode($course['overview_pdf_path']); ?>;
      let content = '<div style="margin-top:20px;border-radius:8px;"><h3 style="font-weight: 500">Course Overview</h3>';
      if (overviewPath && overviewPath !== '') {
        content += `<iframe src="${overviewPath}" style="width:100%; height:500px; border:none;" title="Course Overview"></iframe>`;
      } else {
        content += '<p>No course overview uploaded yet.</p>';
      }
      content += '</div>';
      dynamicContent.innerHTML = content;

    } else if (target === 'student-list') {
      const filtered = studentsData.filter(s => parseInt(s.courseID) === parseInt(selectedCourseID));
      let tableHTML = `<div style="width:100%;">            <h2 style="font-weight: 500; color: #000; text-align: center; padding: 20px 0;"> Enrolled Students (Total: ${filtered.length})</h2>
      <table><thead><tr><th>Student Name</th></tr></thead><tbody>`;
      filtered.forEach(s => tableHTML += `<tr><td>${s.firstName} ${s.lastName}</td></tr>`);
      tableHTML += '</tbody></table></div>';
      dynamicContent.innerHTML = filtered.length > 0 ? tableHTML : '<p>No students enrolled yet for this course.</p>';

    } else if (target === 'quizzes') {
      dynamicContent.innerHTML = '<p>Quizzes management will appear here.</p>';

    } else if (target === 'textbook') {
      const textbookPath = <?= json_encode($course['textbook_pdf_path']); ?>;
      let content = '<div style="margin-top:20px;border-radius:8px;"><h2 style="font-weight: 500; text-align: center;">Textbook</h2>';
      if (textbookPath && textbookPath !== '') {
        content += `<iframe src="${textbookPath}" style="width:100%; height:500px; border:none;" title="Textbook"></iframe>`;
      } else {
        content += '<p>No textbook uploaded yet.</p>';
      }
      content += '</div>';
      dynamicContent.innerHTML = content;

    } else if (target === 'upload') {
      dynamicContent.innerHTML = `
        <div id="forms">
      <table style="width:100%; border-collapse: separate; border-spacing: 0 10px;">
            <h2 style="font-weight: 500; color: #000; text-align: center; padding: 50px 0;"> Upload Course Materials</h2>

      <tbody>
      <!-- Upload Course Overview -->
      <tr>
        <td style="width:30%;"><label for="courseContent">Course Content (PDF):</label></td>
        <td style="width:60%;"><input type="file" id="materialFile" name="materialFile" accept="application/pdf" required></td>
        <td><form action="add_material.php" method="POST" enctype="multipart/form-data" style="display:flex; align-items:center; gap:10px; margin-top:15px;"> 
            <input type="hidden" name="courseID" value="<?= $courseID; ?>"> 
            <input type="hidden" name="materialFileHidden" id="materialFileHidden">
            <button type="submit" style="margin-top: 0; padding: 5px 10px;">Upload</button> 
          </form></td>
      </tr>
      <tr>
        <td style="width:30%;"><label for="overviewFile">Course Overview (PDF):</label></td>
        <td style="width:40%;"><input type="file" id="overviewFile" name="overviewFile" accept="application/pdf" required></td>
        <td><form action="upload_overview.php" method="POST" enctype="multipart/form-data" style="margin:0;">
              <input type="hidden" name="courseID" value="<?= $courseID; ?>">
              <input type="hidden" name="overviewFileHidden" id="overviewFileHidden">
              <button type="submit" style="margin-top: 0; padding: 5px 10px;">Upload</button>
            </form></td>
      </tr>

      <!-- Upload Textbook -->
      <tr>
        <td style="width:30%;"><label for="textbookFile">Textbook (PDF):</label></td>
        <td style="width:40%;"><input type="file" id="textbookFile" name="textbookFile" accept="application/pdf" required></td>
        <td><form action="upload_textbook.php" method="POST" enctype="multipart/form-data" style="margin:0;">
              <input type="hidden" name="courseID" value="<?= $courseID; ?>">
              <input type="hidden" name="textbookFileHidden" id="textbookFileHidden">
              <button type="submit" style="margin-top: 0; padding: 5px 10px;">Upload</button>
            </form></td>
      </tr>
    </tbody>
  </table>
</div>
`;
    }
  });
});
</script>
</body>
</html>
