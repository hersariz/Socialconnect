    </div> <!-- Akhir container -->
    
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-share-nodes me-2"></i>SocialConnect</h5>
                    <p>Platform media sosial sederhana untuk menghubungkan semua orang.</p>
                </div>
                <div class="col-md-3">
                    <h5>Tautan</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo $relative_path; ?>index.php" class="text-white">Beranda</a></li>
                        <li><a href="<?php echo $relative_path; ?>pages/about.php" class="text-white">Tentang Kami</a></li>
                        <li><a href="<?php echo $relative_path; ?>pages/privacy.php" class="text-white">Kebijakan Privasi</a></li>
                        <li><a href="<?php echo $relative_path; ?>pages/terms.php" class="text-white">Ketentuan Layanan</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Ikuti Kami</h5>
                    <div class="d-flex mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> SocialConnect. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo $relative_path; ?>assets/js/main.js"></script>
</body>
</html> 