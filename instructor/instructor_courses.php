<?php
session_start();
require_once('../database.php');

// Make sure user is logged in as instructor
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== "Instructor") {
    header("Location: ../login_form.php");
    exit();
}

$instructorID = $_SESSION['userID'];

// Fetch courses for this instructor
$queryCourses = 'SELECT * FROM courses WHERE instructorID = :id';
$statement = $db->prepare($queryCourses);
$statement->bindValue(':id', $instructorID);
$statement->execute();
$courses = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Instructor</title>
    <script src="../scripts/app.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/web-capstone/css/app.css">
  </head>
  <body data-role="<?php echo htmlspecialchars($role); ?>">
    <?php include('instructor_header.php'); ?>
      <div class="container my-5 d-flex justify-content-center">
        
        <div class="row g-4"> <!-- Increased gutter from g-1 to g-4 -->
          <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $course): ?>
              <div class="col-md-5 d-flex justify-content-center"> <!-- centers card in column -->
                <div class="card shadow-sm border-0" style="width: 280px; height: 230px;">
                  <?php if (!empty($course['imageName'])): ?>
                    <img src="<?php echo '/web-capstone/images/' . htmlspecialchars($course['imageName']); ?>" 
                        class="card-img-top" alt="Course Image" style="height: 100px; object-fit: cover;">
                  <?php else: ?>
                    <img src="/web-capstone/images/placeholder.jpg" 
                        class="card-img-top" alt="No Image" style="height: 100px; object-fit: cover;">
                  <?php endif; ?>

                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($course['courseName']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($course['courseDescription']); ?></p>

                    <div class="mt-auto d-flex justify-content-between">
                      <a href="upload_materials.php?courseID=<?php echo $course['courseID']; ?>" 
                        class="btn btn-primary btn-sm" style="background: #2f65f9;">Add Materials</a>
                      <a href="edit_course.php?courseID=<?php echo $course['courseID']; ?>" 
                        class="btn btn-secondary btn-sm">Edit</a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="alert alert-warning">No courses assigned.</div>
            </div>
          <?php endif; ?>
        </div>
              <!-- <div class="row"> <a href="instructor_dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a> </div> </div> -->

      </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
