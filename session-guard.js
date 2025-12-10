// session-guard.js
fetch("../session-check.php")
  .then(res => res.json())
  .then(data => {
    if (!data.loggedIn) {
      window.location.href = "../e-comerce/login.html";
    }
  })
  .catch(() => {
    window.location.href = "../e-comerce/login.html";
  });
