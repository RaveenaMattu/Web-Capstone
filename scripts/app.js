console.log("JS loaded");

/**************************************/
/*           SHOW ADMIN LOGIN         */
/**************************************/
let adminLightbox = document.querySelector('#adminLightbox');
function openAdminLogin() {
  console.log('Opening admin login lightbox');
  if(adminLightbox) adminLightbox.style.visibility = 'visible';
}

/**************************************/
/*      SHOW LOGOUT / PROFILE MENU    */
/**************************************/
document.addEventListener("DOMContentLoaded", () => {
  const profileWrapper = document.querySelector('.profile-wrapper');
  const logOutBox = document.querySelector('.logOutBox');

  if (profileWrapper && logOutBox) {
    // Show logout box when hovering profile wrapper
    profileWrapper.addEventListener("mouseenter", () => {
      logOutBox.style.visibility = "visible";
    });

    // Hide logout box only when NOT hovering profile wrapper OR logOutBox
    profileWrapper.addEventListener("mouseleave", (e) => {
      // Delay to allow moving to the logOutBox
      setTimeout(() => {
        if (!profileWrapper.matches(':hover') && !logOutBox.matches(':hover')) {
          logOutBox.style.visibility = "hidden";
        }
      }, 50);
    });

    // Also hide when leaving the logout box itself
    logOutBox.addEventListener("mouseleave", () => {
      if (!profileWrapper.matches(':hover')) {
        logOutBox.style.visibility = "hidden";
      }
    });
  }
});


/**************************************/
/*         OPEN UPDATE PROFILE        */
/**************************************/
function openUpdateProfile() {
  const role = document.body.dataset.role; // role set in PHP
  const modalId = role === 'admin' ? '#updateAdmin'
                 : role === 'Instructor' ? '#updateInstructor'
                 : '#updateStudent';
  const modal = document.querySelector(modalId);
  if(modal) modal.style.visibility = 'visible';
}

function closeUpdateProfile() {
  const role = document.body.dataset.role;
  const modalId = role === 'admin' ? '#updateAdmin'
                 : role === 'Instructor' ? '#updateInstructor'
                 : '#updateStudent';
  const modal = document.querySelector(modalId);
  if(modal) modal.style.visibility = 'hidden';
}

/**************************************/
/*        OPEN DELETE POPUP           */
/**************************************/
const overlay = document.getElementById('overlay');
const deletePopup = document.getElementById('deletePopup');
const popupDeleteForm = document.getElementById('popupDeleteForm');
const popupRecordID = document.getElementById('popupRecordID');

document.querySelectorAll('.deleteForm').forEach(form => {
  form.addEventListener('submit', function(event) {
    event.preventDefault();

    const studentInput = form.querySelector('input[name="studentID"]');
    const instructorInput = form.querySelector('input[name="instructorID"]');
    const courseInput = form.querySelector('input[name="courseID"]');
    const taskInput = form.querySelector('input[name="taskID"]');

    if (studentInput && studentInput.value) {
      popupRecordID.name = 'studentID';
      popupRecordID.value = studentInput.value;
      popupDeleteForm.action = 'manage_student/delete_student.php';  
    } else if (instructorInput && instructorInput.value) {
      popupRecordID.name = 'instructorID';
      popupRecordID.value = instructorInput.value;
      popupDeleteForm.action = 'manage_instructor/delete_instructor.php'; 
    } else if (courseInput && courseInput.value) {
      popupRecordID.name = 'courseID';
      popupRecordID.value = courseInput.value;
      popupDeleteForm.action = 'manage_course/delete_course.php';       
    } else if (taskInput && taskInput.value) {
      popupRecordID.name = 'taskID';
      popupRecordID.value = taskInput.value;
      popupDeleteForm.action = 'manage_task/delete_task.php';       
    } else {
      alert('No valid record selected for deletion.');
      return;
    }

    overlay.style.display = 'block';
    deletePopup.style.display = 'block';
  });
});

function closePopup() {
  overlay.style.display = 'none';
  deletePopup.style.display = 'none';
  popupRecordID.name = '';
  popupRecordID.value = '';
  popupDeleteForm.action = '';
}
