console.log("JS loaded");
/**************************************/
/*           SHOW ADMIN LOGIN         */
/**************************************/
let adminLightbox = document.querySelector('#adminLightbox');

function openAdminLogin() {
  console.log('Opening admin login lightbox');
  adminLightbox.style.visibility = 'visible';
}
/**************************************/
/*           OPEN ADMIN LOGOUT        */
/**************************************/
document.addEventListener("DOMContentLoaded", () => {
  const profileWrapper = document.querySelector('.profile-wrapper');
  const logOutBox = document.querySelector('.logOutBox');

  if (profileWrapper && logOutBox) {
    profileWrapper.addEventListener("mouseenter", () => {
      logOutBox.style.visibility = "visible";
    });

    logOutBox.addEventListener("mouseleave", () => {
      logOutBox.style.visibility = "hidden";
    });
  }
});
/**************************************/
/*             UPDATE ADMIN           */
/**************************************/
let updateAdmin = document.querySelector('#updateAdmin');
function openUpdateAdmin() {
  updateAdmin.style.visibility = 'visible';
}
function closeUpdateAdmin() {
  updateAdmin.style.visibility = 'hidden';
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

    console.log('Clicked delete form', form);
    console.log('Student:', studentInput?.value);
    console.log('Instructor:', instructorInput?.value);
    console.log('Course:', courseInput?.value);

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
    }
    else {
      alert('No valid record selected for deletion.');
    }
    // Show delete popup
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
} // closePopup function

