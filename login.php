<?php
session_start();
// Jika sudah login, langsung arahkan ke index (dashboard)
if(isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Login - Sistem Pajak YASPA</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container-xl px-4">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header justify-content-center text-center pt-4 pb-3">
                                    <h3 class="fw-bold text-primary mb-0">Login YASPA</h3>
                                    <div class="small text-muted mt-2">Sistem Manajemen Pajak PPh 21</div>
                                </div>
                                <div class="card-body">
                                    
                                    <!-- Menampilkan pesan error jika login gagal -->
                                    <?php if(isset($_SESSION['error'])): ?>
                                        <div class="alert alert-danger small p-2 text-center">
                                            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <form action="proses_login.php" method="POST">
                                        <div class="mb-3">
                                            <label class="small mb-1 text-dark fw-bold">NPWP / Username</label>
                                            <input class="form-control" name="npwp" type="text" placeholder="Masukkan NPWP Anda (Atau 'admin')" required autofocus />
                                        </div>
                                        <div class="mb-4">
                                            <label class="small mb-1 text-dark fw-bold">Password</label>
                                            <input class="form-control" name="password" type="password" placeholder="Masukkan password" required />
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <button type="submit" class="btn btn-primary w-100 fw-bold">Login</button>
                                        </div>
                                    </form>

                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small text-muted">Gunakan NPWP Anda dan Password: <b>Pajak2026</b></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>