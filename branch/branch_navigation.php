<header>
    <!----- Navigation | Branch Page ----->
    <div class="custom-nav-header">
        <div class="container align-items-bottom d-flex justify-content-between pt-1">
            <small class="text-white">Branch Panel > Welcome, Staff!</small>
            <div>
                <small class="text-white mt-2" id="time">Day |</small>
                <small class="text-white mt-2" id="time">00:00:00</small>
                <small><span class="badge m-0 text-bg-light text-success">Month DD</span></small>
            </div>
        </div>
    </div>

    <nav class="navbar custom-navbar shadow" id="navbar">
        <div class="container">
            <div class="d-inline-flex align-items-center custom-logo-label">
                <img class="me-2 p-0" src="../img/LyzaVectorLogo.png" alt="Lyza Drugmart" width="35" height="30">

            </div>

            <h2 class=" fw-bold fs-4">Lyza Drugmart</h2>

            <!----- Log Out ----->
            <div>
                <form method="POST" action="backend/redirector.php">
                    <input type="hidden" name="type" value="check-online-status">
                    <button id="printChartButton" class="btn btn-success ms-3 p-2 border-dark-green" type="submit">Check
                        Online
                        Status</button>
                    <a class="btn btn-secondary p-2" type="button" href="index.php">
                        <small><i class="bi bi-box-arrow-right"></i>&nbsp;LOG OUT </small>
                    </a>
                </form>

            </div>
        </div>
    </nav>
</header>