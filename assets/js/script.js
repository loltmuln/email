const loginText = document.querySelector(".title-text .login");
const loginForm = document.querySelector("form.login");
const loginBtn = document.querySelector("label.login");
const signupBtn = document.querySelector("label.signup");
const signupLink = document.querySelector("form .signup-link a");

signupBtn.onclick = () => {
  loginForm.style.marginLeft = "-50%";
  loginText.style.marginLeft = "-50%";
};

loginBtn.onclick = () => {
  loginForm.style.marginLeft = "0%";
  loginText.style.marginLeft = "0%";
};

signupLink.onclick = () => {
  signupBtn.click();
  return false;
};
window.addEventListener("DOMContentLoaded", () => {
  const signupTab = document.getElementById("signup");
  const formInner = document.querySelector(".form-inner");
  const urlParams = new URLSearchParams(window.location.search);

  if (urlParams.has("signup") && signupTab) {
    signupTab.checked = true;

    // Ensure the sliding animation happens visually
    if (formInner) {
      formInner.style.marginLeft = "-100%";
    }
  }
});
