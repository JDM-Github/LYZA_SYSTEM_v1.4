<header>
    <nav class="navbar custom-navbar shadow" id="navbar">
        <div class="container">
            <!----- Logo/Brand ----->
            <div class="d-inline-flex custom-logo-label">
                <img class="me-2 align-self-center" src="img/LyzaVectorLogo.png" alt="Lyza Drugmart" width="35"
                    height="30">
                <h1 class="align-self-center fs-5 pt-1 fw-bold" style="color: var(--green);">LYZA DRUGMART</h1>
            </div>

            <!----- Toggle Offcanvass | Login Form ----->
            <button class="btn rounded-pill custom-btn-success py-2 px-3" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#login-form-offcanvass" aria-controls="login-form-offcanvass">
                <small></i>&nbsp;LOG IN</small>
            </button>
        </div>
    </nav>

    <!----- Offcanvass Body | Login Form ----->
    <div class="offcanvas offcanvas-end" data-bs-backdrop="static" tabindex="-1" id="login-form-offcanvass"
        aria-labelledby="login-form-label">

        <div class="offcanvas-header mb-0 pb-0">
            <!----- Close Form ----->
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <!----- Login Form Body ----->
        <div class="offcanvas-body">
            <?php
            include "client/client_login.php";
            ?>
        </div>
    </div>
</header>