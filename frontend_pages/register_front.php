<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../css/register.css">

</head>

<body>

    <div class="register-card">

        <div class="logo">

            <h1>Create Account</h1>

            <p>Start your journey with us</p>

        </div>

        <form id="registerForm">

            <div class="form-group">

                <input type="text" id="Name" placeholder="Full Name" required>

            </div>

            <div class="form-group">

                <input type="email" id="Email" placeholder="Email Address" required>

            </div>

            <div class="form-group">

                <input type="tel" id="Phone_No" placeholder="Phone Number" required>

            </div>

            <div class="form-group password-group">

                <input type="password" id="Password" placeholder="Password" required>

                <span class="eye" onclick="togglePassword('Password', this)">

                    <i class="bi bi-eye"></i>

                </span>

            </div>

            <div class="form-group password-group">

                <input type="password" id="ConfirmPassword" placeholder="Confirm Password" required>

                <span class="eye" onclick="togglePassword('ConfirmPassword', this)">

                    <i class="bi bi-eye"></i>

                </span>

            </div>

            <button type="submit" class="register-btn">

                Create Account

            </button>

        </form>

        <div id="message" class="mt-3 text-center"></div>
         <div class="footer-text">
            Already have an account?
            <a href="login_front.php">
                Login
            </a>
        </div>
    </div>

    <script>

        function togglePassword(id, element) {

            const input =
                document.getElementById(id);

            const icon =
                element.querySelector("i");

            if (input.type === "password") {

                input.type = "text";

                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");

            } else {

                input.type = "password";

                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");

            }
        }

        document
            .getElementById("registerForm")
            .addEventListener("submit", async (e) => {

                e.preventDefault();

                const data = {

                    name:
                        document.getElementById("Name").value,

                    email:
                        document.getElementById("Email").value,

                    phone_no:
                        document.getElementById("Phone_No").value,

                    password:
                        document.getElementById("Password").value,

                    confirm_password:
                        document.getElementById("ConfirmPassword").value
                };

                try {
                    const response = await fetch(
                        "../api/login_signup/register_backend.php",
                        {
                            method: "POST",
                            body: JSON.stringify(data)
                        }
                    );

                    const result = await response.json();

                    const message =
                        document.getElementById("message");

                    message.innerText =
                        result.message;

                    if (result.success) {

                        message.className =
                            "mt-3 text-success text-center";

                        setTimeout(() => {

                            window.location.href =
                                "login_front.php";

                        }, 1000);

                    } else {

                        message.className =
                            "mt-3 text-danger text-center";
                    }

                } catch (error) {

                    console.error(error);

                    document.getElementById("message").innerText =
                        "Something went wrong.";
                }

            });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>