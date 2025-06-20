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

  const overlay = document.querySelector('#overlay');
  const deletePopup = document.querySelector('#deletePopup');
  const popupInstructorID = document.querySelector('#popupInstructorID');
  const deleteForm = document.querySelectorAll('.deleteForm');

  function showDeletePopup(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const instructorID = form.querySelector('input[name="instructorID"]').value;
    popupInstructorID.value = instructorID;
    overlay.style.display = 'block';
    deletePopup.style.display = 'block';
  }

  document.querySelectorAll('.deleteForm').forEach(form => {
    form.addEventListener('submit', showDeletePopup);
  });

  function closePopup() {
    deletePopup.style.display = 'none';
    overlay.style.display = 'none';
    popupStudentID.value = '';
  }