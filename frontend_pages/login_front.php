<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet"
          href="../css/login.css">

</head>

<body>

    <div class="login-card">

        <div class="logo">

            <h1>Welcome Back</h1>

            <p>Login to continue</p>

        </div>

        <form id="loginForm">

            <div class="form-group">

                <input
                    type="email"
                    id="Email"
                    placeholder="Email Address"
                    required>

            </div>

            <div class="form-group password-group">

                <input
                    type="password"
                    id="Password"
                    placeholder="Password"
                    required>

                <span
                    class="eye"
                    onclick="togglePassword('Password', this)">

                    <i class="bi bi-eye"></i>

                </span>

            </div>

            <button
                type="submit"
                class="login-btn">

                Login

            </button>

        </form>

        <div
            id="message"
            class="mt-3 text-center">
        </div>

        <div class="footer-text">

            Don't have an account?

            <a href="register_front.php">
                Register
            </a>

        </div>

    </div>

    <script>

        function togglePassword(id, element){

            const input =
                document.getElementById(id);

            const icon =
                element.querySelector("i");

            if(input.type === "password"){

                input.type = "text";

                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");

            }else{

                input.type = "password";

                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }

        document
            .getElementById("loginForm")
            .addEventListener("submit", async (e) => {

                e.preventDefault();

                const data = {

                    email:
                        document.getElementById("Email").value,

                    password:
                        document.getElementById("Password").value
                };

                try {

                    const response =
                        await fetch(
                            "../api/login_signup/login_backend.php",
                            {
                                method: "POST",
                                body: JSON.stringify(data)
                            }
                        );

                    const result =
                        await response.json();

                    const message =
                        document.getElementById("message");

                    message.innerText =
                        result.message;

                    if(result.success){

                        message.className =
                            "mt-3 text-success text-center";

                        localStorage.setItem(
                            "user",
                            JSON.stringify(result.user)
                        );

                        setTimeout(() => {

                            window.location.href =
                                "dashboard_front.php";

                        }, 1000);

                    }else{

                        message.className =
                            "mt-3 text-danger text-center";
                    }

                } catch(error){

                    document
                        .getElementById("message")
                        .innerText =
                        "Something went wrong.";

                    console.error(error);
                }

            });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
