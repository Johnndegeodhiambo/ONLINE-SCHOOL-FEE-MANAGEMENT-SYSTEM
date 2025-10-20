</main>
<footer class="site-footer">
  <div class="footer-content">
    <p>&copy; <?= date('Y') ?> Online School Fee Management System (OSFMS). All rights reserved.</p>
    <div class="footer-links">
      <a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a>
      <a href="#"><i class="fas fa-file-contract"></i> Terms of Service</a>
      <a href="mailto:support@osfms.com"><i class="fas fa-envelope"></i> Contact Support</a>
    </div>
  </div>
</footer>

<style>
/* =========================
   Footer Styles
========================= */
.site-footer {
  background: #2c3e50; /* Dark blue/gray */
  color: #ecf0f1;       /* Light text */
  padding: 20px 0;
  margin-top: 40px;
  text-align: center;
  font-family: Arial, sans-serif;
}

.site-footer .footer-content {
  width: 90%;
  max-width: 1200px;
  margin: auto;
}

.site-footer p {
  margin: 0;
  font-size: 14px;
  color: #bdc3c7;
}

.site-footer .footer-links {
  margin-top: 10px;
}

.site-footer .footer-links a {
  color: #ecf0f1;
  text-decoration: none;
  margin: 0 10px;
  font-size: 14px;
  transition: color 0.3s ease;
}

.site-footer .footer-links a i {
  margin-right: 6px;
}

.site-footer .footer-links a:hover {
  color: #1abc9c; /* Teal hover color */
}

/* Responsive footer */
@media (max-width: 600px) {
  .site-footer .footer-links {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 15px;
  }
}
</style>

</body>
</html>
