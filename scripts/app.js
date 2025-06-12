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