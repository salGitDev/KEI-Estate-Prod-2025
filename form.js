document.querySelector("form").addEventListener("submit", function (event) {
  const form = this;
  const errorName = document.getElementById("errorName");
  const errorEmail = document.getElementById("errorEmail");
  const errorMsg = document.getElementById("errorMsg");

  const name = document.getElementById("name").value.trim();
  const email = document.getElementById("email").value.trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const message = document.getElementById("message").value.trim();

  // Reset all error messages
  errorName.style.display = "none";
  errorEmail.style.display = "none";
  errorMsg.style.display = "none";

  // Sequential validation logic
  if (name === "") {
    event.preventDefault();
    errorName.style.display = "block";
    document.getElementById("nameError").innerText =
      "Name is required in this field";
  } else if (!emailRegex.test(email)) {
    event.preventDefault();
    errorEmail.style.display = "block";
    document.getElementById("emailError").innerText = "Invalid email format";
  } else if (message === "") {
    event.preventDefault();
    errorMsg.style.display = "block";
    document.getElementById("messageError").innerText =
      "The message field is required";
  }
  setTimeout(() => {
    name.value = "";
    email.value = "";
    form.reset();
  }, 5000);
});

document.addEventListener("DOMContentLoaded", () => {
  fetch("/social.php")
    .then((res) => res.json())
    .then((data) => {
      document.getElementById("facebookId").href = data.facebook;
      document.getElementById("whatsappId").href = data.whatsapp;
      document.getElementById("linkedinId").href = data.linkedin;
    })
    .catch((err) => console.error("Could not load social media links:", err));
});
