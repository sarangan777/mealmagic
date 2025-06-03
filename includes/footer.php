</div>
  </main>
  
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-logo">
          <span class="logo-icon"><i class="fas fa-utensils"></i></span>
          <span class="logo-text">MealMagic</span>
          <p>Delicious Sri Lankan cuisine delivered to your doorstep</p>
        </div>
        
        <div class="footer-links">
          <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
              <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
              <li><a href="<?php echo SITE_URL; ?>/menu.php">Menu</a></li>
              <li><a href="<?php echo SITE_URL; ?>/cart.php">Cart</a></li>
              <li><a href="<?php echo SITE_URL; ?>/order-status.php">Track Order</a></li>
            </ul>
          </div>
          
          <div class="footer-section">
            <h3>Cities We Serve</h3>
            <ul>
              <li>Colombo</li>
              <li>Kandy</li>
              <li>Galle</li>
              <li>Jaffna</li>
              <li>Batticaloa</li>
              <li>Anuradhapura</li>
            </ul>
          </div>
          
          <div class="footer-section">
            <h3>Contact Us</h3>
            <ul class="contact-info">
              <li><i class="fas fa-phone"></i> +94 11 234 5678</li>
              <li><i class="fas fa-envelope"></i> info@mealmagic.lk</li>
              <li><i class="fas fa-map-marker-alt"></i> 123 Temple Road, Colombo, Sri Lanka</li>
            </ul>
          </div>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> MealMagic. All rights reserved.</p>
        <div class="social-links">
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        </div>
      </div>
    </div>
  </footer>
  
  <!-- Custom JavaScript -->
  <script src="<?php echo SITE_URL; ?>/assets/js/scripts.js"></script>
  
  <!-- Language switcher script -->
  <script>
    document.getElementById('lang-select').addEventListener('change', function() {
      window.location.href = window.location.pathname + '?lang=' + this.value;
    });
  </script>
</body>
</html>