<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.html');
    exit();
}
$displayName = htmlspecialchars($_SESSION['usuario']);
$avatar = htmlspecialchars($_SESSION['avatar'] ?? 'imagenes/default-avatar.png');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>JSMS - Tienda Online</title>
  <script src="https://www.paypal.com/sdk/js?client-id=test&currency=PEN"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Montserrat:wght@700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">

  <style>
    :root {
      --bg-primary: #0a1428;
      --bg-secondary: #0f1e3a;
      --accent: #00d9ff;
      --accent-glow: #0099cc;
      --success: #00d4aa;
      --text-primary: #e8f0ff;
      --text-muted: #8a99b4;
      --border-color: rgba(0, 217, 255, 0.1);
      --glass-bg: rgba(15, 30, 58, 0.5);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      background: linear-gradient(135deg, #0a1428 0%, #0d1f3c 50%, #0a1428 100%);
      color: var(--text-primary);
      font-family: 'Inter', sans-serif;
      line-height: 1.6;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }

    .topbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 75px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 30px;
      background: linear-gradient(90deg, rgba(10, 20, 40, 0.95), rgba(13, 31, 60, 0.95));
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--border-color);
      z-index: 1000;
      box-shadow: 0 8px 32px rgba(0, 217, 255, 0.05);
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 15px;
      text-decoration: none;
      color: inherit;
      font-weight: 800;
      font-size: 1.3rem;
      letter-spacing: 1px;
    }

    .brand img {
      height: 50px;
      width: 50px;
      border-radius: 10px;
      border: 2px solid var(--accent);
      object-fit: cover;
      box-shadow: 0 0 15px rgba(0, 217, 255, 0.2);
    }

    .nav-center {
      display: flex;
      gap: 30px;
    }

    .nav-center a {
      color: var(--text-muted);
      text-decoration: none;
      font-weight: 600;
      font-size: 0.95rem;
      padding: 8px 12px;
      border-radius: 6px;
      transition: all 0.3s ease;
      border-bottom: 2px solid transparent;
    }

    .nav-center a:hover, .nav-center a.active {
      color: var(--accent);
      border-bottom-color: var(--accent);
      background: rgba(0, 217, 255, 0.05);
    }

    .user-section {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .user-badge {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 6px 14px;
      background: var(--glass-bg);
      border: 1px solid var(--border-color);
      border-radius: 50px;
      text-decoration: none;
      color: inherit;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .user-badge:hover {
      background: rgba(0, 217, 255, 0.1);
      border-color: var(--accent);
    }

    .user-badge img {
      height: 38px;
      width: 38px;
      border-radius: 50%;
      border: 2px solid var(--accent);
      object-fit: cover;
    }

    .user-badge .name {
      font-weight: 700;
      font-size: 0.9rem;
    }

    .btn-close {
      padding: 8px 16px;
      background: linear-gradient(135deg, rgba(0, 217, 255, 0.1), rgba(0, 153, 204, 0.1));
      border: 1.5px solid var(--accent);
      border-radius: 8px;
      color: var(--accent);
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .btn-close:hover {
      background: var(--accent);
      color: var(--bg-primary);
      box-shadow: 0 0 20px rgba(0, 217, 255, 0.3);
    }

    main {
      padding: 110px 40px 60px;
      max-width: 1300px;
      margin: 0 auto;
    }

    .hero {
      display: flex;
      align-items: center;
      gap: 50px;
      padding: 50px;
      background: linear-gradient(135deg, var(--bg-secondary) 0%, rgba(0, 217, 255, 0.03) 100%);
      border-radius: 20px;
      border: 1px solid var(--border-color);
      box-shadow: 0 20px 60px rgba(0, 217, 255, 0.08);
      margin-bottom: 60px;
      overflow: hidden;
      position: relative;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -20%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(0, 217, 255, 0.15), transparent);
      border-radius: 50%;
      pointer-events: none;
    }

    .hero-left {
      flex: 1;
      position: relative;
      z-index: 1;
    }

    .hero-left h2 {
      color: var(--accent);
      font-weight: 700;
      font-size: 1rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      margin-bottom: 15px;
    }

    .hero-left h1 {
      font-size: 3rem;
      margin-bottom: 20px;
      line-height: 1.2;
      background: linear-gradient(135deg, var(--text-primary), var(--accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-left p {
      font-size: 1.1rem;
      color: var(--text-muted);
      margin-bottom: 30px;
      line-height: 1.8;
    }

    .hero-cta {
      display: flex;
      gap: 15px;
    }

    .btn-primary {
      padding: 14px 28px;
      background: linear-gradient(135deg, var(--accent), var(--accent-glow));
      border: none;
      border-radius: 10px;
      color: var(--bg-primary);
      font-weight: 800;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 10px 30px rgba(0, 217, 255, 0.2);
      text-decoration: none;
      display: inline-block;
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(0, 217, 255, 0.3);
    }

    .btn-secondary {
      padding: 14px 28px;
      background: transparent;
      border: 2px solid var(--accent);
      border-radius: 10px;
      color: var(--accent);
      font-weight: 800;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-secondary:hover {
      background: rgba(0, 217, 255, 0.1);
      box-shadow: 0 10px 30px rgba(0, 217, 255, 0.15);
    }

    .hero-right {
      flex: 0 0 360px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .hero-right img {
      width: 100%;
      max-width: 340px;
      border-radius: 15px;
      border: 2px solid var(--border-color);
      box-shadow: 0 30px 80px rgba(0, 217, 255, 0.15);
      animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }

    .section {
      margin-bottom: 60px;
    }

    .section-header {
      margin-bottom: 40px;
    }

    .section-title {
      font-size: 2rem;
      font-weight: 800;
      margin-bottom: 10px;
    }

    .section-subtitle {
      color: var(--text-muted);
      font-size: 1.05rem;
    }

    .filters {
      display: flex;
      gap: 12px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }

    .filter-btn {
      padding: 10px 20px;
      background: var(--glass-bg);
      border: 1.5px solid var(--border-color);
      border-radius: 8px;
      color: var(--text-muted);
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
      font-size: 0.95rem;
    }

    .filter-btn:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    .filter-btn.active {
      background: linear-gradient(135deg, rgba(0, 217, 255, 0.2), rgba(0, 153, 204, 0.2));
      border-color: var(--accent);
      color: var(--accent);
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 24px;
    }

    .product-card {
      background: var(--glass-bg);
      border: 1px solid var(--border-color);
      border-radius: 15px;
      padding: 20px;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      position: relative;
    }

    .product-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 300px;
      height: 300px;
      background: radial-gradient(circle, rgba(0, 217, 255, 0.1), transparent);
      border-radius: 50%;
      pointer-events: none;
    }

    .product-card:hover {
      transform: translateY(-10px);
      border-color: var(--accent);
      box-shadow: 0 20px 50px rgba(0, 217, 255, 0.15);
      background: linear-gradient(135deg, rgba(0, 217, 255, 0.08), rgba(13, 31, 60, 0.5));
    }

    .product-image {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 15px;
      transition: transform 0.3s ease;
    }

    .product-card:hover .product-image {
      transform: scale(1.05);
    }

    .product-name {
      font-weight: 700;
      font-size: 1.1rem;
      margin-bottom: 8px;
      position: relative;
      z-index: 1;
    }

    .product-desc {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: 12px;
      flex-grow: 1;
      position: relative;
      z-index: 1;
    }

    .product-price {
      font-weight: 800;
      font-size: 1.4rem;
      color: var(--success);
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
    }

    .product-btn {
      padding: 10px 16px;
      background: linear-gradient(135deg, var(--accent), var(--accent-glow));
      border: none;
      border-radius: 8px;
      color: var(--bg-primary);
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      z-index: 1;
    }

    .product-btn:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 20px rgba(0, 217, 255, 0.2);
    }

    .about-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      align-items: center;
      padding: 50px;
      background: linear-gradient(135deg, var(--bg-secondary) 0%, rgba(0, 217, 255, 0.03) 100%);
      border-radius: 20px;
      border: 1px solid var(--border-color);
    }

    .about-text h3 {
      font-size: 1.5rem;
      margin-bottom: 15px;
    }

    .about-text p {
      color: var(--text-muted);
      font-size: 1.05rem;
      line-height: 1.9;
      margin-bottom: 20px;
    }

    .about-features {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .feature-item {
      display: flex;
      gap: 15px;
      align-items: flex-start;
    }

    .feature-icon {
      width: 50px;
      height: 50px;
      background: rgba(0, 217, 255, 0.1);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--accent);
      font-size: 1.5rem;
      flex-shrink: 0;
    }

    .feature-text h4 {
      font-weight: 700;
      margin-bottom: 5px;
    }

    .feature-text p {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin: 0;
    }

    .contact-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      padding: 50px;
      background: linear-gradient(135deg, var(--bg-secondary) 0%, rgba(0, 217, 255, 0.03) 100%);
      border-radius: 20px;
      border: 1px solid var(--border-color);
    }

    .contact-form {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-group label {
      font-weight: 700;
      font-size: 0.95rem;
    }

    .form-group input,
    .form-group textarea {
      padding: 12px 16px;
      background: rgba(255, 255, 255, 0.05);
      border: 1.5px solid var(--border-color);
      border-radius: 8px;
      color: var(--text-primary);
      font-family: 'Inter', sans-serif;
      font-size: 0.95rem;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--accent);
      background: rgba(0, 217, 255, 0.05);
      box-shadow: 0 0 20px rgba(0, 217, 255, 0.1);
    }

    .form-actions {
      display: flex;
      gap: 12px;
    }

    .form-actions .btn-primary {
      flex: 1;
    }

    .form-actions .btn-secondary {
      flex: 1;
    }

    .contact-info h3 {
      font-size: 1.5rem;
      margin-bottom: 30px;
    }

    .contact-item {
      margin-bottom: 25px;
    }

    .contact-item p {
      color: var(--text-muted);
      margin: 0;
    }

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 30px;
    }

    .social-links a {
      width: 50px;
      height: 50px;
      background: var(--glass-bg);
      border: 1.5px solid var(--border-color);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--accent);
      text-decoration: none;
      transition: all 0.3s ease;
      font-size: 1.2rem;
    }

    .social-links a:hover {
      background: rgba(0, 217, 255, 0.2);
      border-color: var(--accent);
      transform: scale(1.1);
    }

    .footer {
      margin-top: 80px;
      padding: 40px;
      text-align: center;
      border-top: 1px solid var(--border-color);
      color: var(--text-muted);
    }

    .whatsapp-float {
      position: fixed;
      right: 30px;
      bottom: 30px;
      z-index: 999;
      animation: pulse 2s infinite;
    }

    .whatsapp-float img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      box-shadow: 0 10px 30px rgba(0, 217, 255, 0.2);
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }

    @media (max-width: 1024px) {
      main { padding: 100px 25px 40px; }
      .hero { flex-direction: column; gap: 30px; }
      .hero-right { flex: 0 0 100%; }
      .about-container, .contact-container { grid-template-columns: 1fr; }
      .nav-center { display: none; }
    }

    @media (max-width: 640px) {
      main { padding: 85px 16px 30px; }
      .topbar { padding: 0 16px; }
      .hero-left h1 { font-size: 2rem; }
      .hero-cta { flex-direction: column; }
      .product-grid { grid-template-columns: 1fr; }
      .section-title { font-size: 1.5rem; }
      .user-section { gap: 8px; }
      .user-badge .name { display: none; }
    }
  </style>
</head>
<body>
  <div class="topbar">
    <a href="#home" class="brand">
      <img src="imagenes/logo1.png" alt="JSMS Logo">
      <span>JSMS</span>
    </a>

    <div class="nav-center">
      <a href="#home" class="active">Inicio</a>
      <a href="#products">Productos</a>
      <a href="#about">Nosotros</a>
      <a href="#contact">Contacto</a>
    </div>

    <div class="user-section">
      <a href="perfil.php" class="user-badge" title="Mi perfil">
        <img src="<?php echo $avatar; ?>" alt="Tu avatar" onerror="this.src='imagenes/default-avatar.png'">
        <span class="name"><?php echo $displayName; ?></span>
      </a>
      <a href="logout.php" class="btn-close">
        <i class="fas fa-sign-out-alt"></i> Cerrar
      </a>
    </div>
  </div>

  <main>
    <section class="hero" id="home">
      <div class="hero-left">
        <h2>Bienvenido a JSMS</h2>
        <h1>Tienda de Artículos Esenciales</h1>
        <p>Calidad garantizada, precios justos y envío local. Todo lo que necesitas en un solo lugar.</p>
        <div class="hero-cta">
          <a href="#products" class="btn-primary">Explorar Catálogo</a>
          <a href="#contact" class="btn-secondary">Contáctanos</a>
        </div>
      </div>
      <div class="hero-right">
        <img src="imagenes/limpieza.png" alt="Productos JSMS">
      </div>
    </section>

    <section class="section" id="products">
      <div class="section-header">
        <h2 class="section-title">Nuestros Productos</h2>
        <p class="section-subtitle">Artículos de primera necesidad con calidad premium</p>
      </div>

      <div class="filters">
        <button class="filter-btn active" data-filter="all">Todos</button>
        <button class="filter-btn" data-filter="alimentos">Alimentos</button>
        <button class="filter-btn" data-filter="higiene">Higiene</button>
        <button class="filter-btn" data-filter="hogar">Hogar</button>
      </div>

      <div class="product-grid" id="product-grid">
      </div>
    </section>

    <section class="section" id="about">
      <div class="section-header">
        <h2 class="section-title">¿Quiénes Somos?</h2>
        <p class="section-subtitle">Conoce más sobre JSMS</p>
      </div>

      <div class="about-container">
        <div class="about-text">
          <h3>Comprometidos con la Calidad</h3>
          <p>JSMS es una empresa dedicada a la distribución de artículos de primera necesidad. Nos especializamos en alimentos, productos de limpieza e higiene personal con precios competitivos y disponibilidad garantizada.</p>
          <p>Nuestro objetivo es ser tu tienda de confianza, con productos seleccionados cuidadosamente y un servicio atento a tus necesidades.</p>
        </div>

        <div class="about-features">
          <div class="feature-item">
            <div class="feature-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="feature-text">
              <h4>Productos Frescos</h4>
              <p>Selección cuidada con control de calidad riguroso</p>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon">
              <i class="fas fa-tag"></i>
            </div>
            <div class="feature-text">
              <h4>Precios Competitivos</h4>
              <p>Los mejores precios con promociones periódicas</p>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon">
              <i class="fas fa-truck"></i>
            </div>
            <div class="feature-text">
              <h4>Entrega Rápida</h4>
              <p>Servicio de envío local dentro de la ciudad</p>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon">
              <i class="fas fa-headset"></i>
            </div>
            <div class="feature-text">
              <h4>Soporte 24/7</h4>
              <p>Atención al cliente siempre disponible</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="contact">
      <div class="section-header">
        <h2 class="section-title">Contacto</h2>
        <p class="section-subtitle">¿Tienes preguntas? Nos encantaría escucharte</p>
      </div>

      <div class="contact-container">
        <form class="contact-form" id="contactForm">
          <div class="form-group">
            <label for="name">Nombre Completo</label>
            <input id="name" name="name" type="text" placeholder="Tu nombre" required>
          </div>

          <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input id="email" name="email" type="email" placeholder="tu@email.com" required>
          </div>

          <div class="form-group">
            <label for="phone">Teléfono</label>
            <input id="phone" name="phone" type="tel" placeholder="999 999 999">
          </div>

          <div class="form-group">
            <label for="message">Mensaje</label>
            <textarea id="message" name="message" rows="5" placeholder="Cuéntanos cómo podemos ayudarte..." required></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">Enviar Mensaje</button>
            <button type="reset" class="btn-secondary">Limpiar</button>
          </div>
        </form>

        <div class="contact-info">
          <h3>Información de Contacto</h3>

          <div class="contact-item">
            <p><strong> Teléfono:</strong></p>
            <p>(01) 234-5678</p>
          </div>

          <div class="contact-item">
            <p><strong> Dirección:</strong></p>
            <p>Calle Daniel Alcides 123, Nauta</p>
          </div>

          <div class="contact-item">
            <p><strong> Horario de Atención:</strong></p>
            <p>Lunes a Viernes: 9:00 AM - 6:00 PM<br>Sábados: 10:00 AM - 4:00 PM</p>
          </div>

          <div class="social-links">
            <a href="https://www.facebook.com/" target="_blank" title="Facebook">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://www.instagram.com/" target="_blank" title="Instagram">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="https://x.com/" target="_blank" title="Twitter">
              <i class="fab fa-twitter"></i>
            </a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <p> <span id="year"></span> JSMS  Todos los derechos reservados | Tienda de Artículos de Primera Necesidad</p>
  </footer>

  <a href="https://wa.me/51988469378?text=Hola%20JSMS,%20quisiera%20m%C3%A1s%20informaci%C3%B3n" class="whatsapp-float" target="_blank" title="Contáctanos por WhatsApp">
    <img src="imagenes/whatsapp.webp" alt="WhatsApp">
  </a>

  <script>
    document.getElementById('year').textContent = new Date().getFullYear();

    document.querySelectorAll('.nav-center a').forEach(link => {
      link.addEventListener('click', function() {
        document.querySelectorAll('.nav-center a').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
      });
    });

    document.getElementById('contactForm')?.addEventListener('submit', function(e) {
      e.preventDefault();
      alert('¡Gracias por tu mensaje! Nos pondremos en contacto pronto.');
      this.reset();
    });
  </script>
    <!-- BOTÓN CARRITO FLOTANTE -->
  <button id="cartBtn" class="cart-btn-float" aria-label="Abrir carrito" title="Ver carrito">
    <i class="fas fa-shopping-cart"></i>
    <span id="cart-count" class="cart-count">0</span>
  </button>

  <!-- MODAL CARRITO -->
  <div id="cartModal" class="cart-modal" aria-hidden="true">
    <div class="cart-panel">
      <header class="cart-header">
        <h3>Tu Carrito</h3>
        <button id="closeCart" class="cart-close-btn" aria-label="Cerrar carrito">
          <i class="fas fa-times"></i>
        </button>
      </header>
      <div id="cartItems" class="cart-items">
        <!-- Items inyectados por JS -->
      </div>
      <div class="cart-footer">
        <div class="cart-summary">
          <div class="cart-total-line">
            <span>Total:</span>
            <span class="cart-total">S/ 0.00</span>
          </div>
        </div>
        <div class="payment-methods">
          <button id="yapeBtn" class="payment-btn yape-btn">
            <i class="fas fa-mobile-alt"></i> Pagar con Yape
          </button>
          <button id="paypalBtn" class="payment-btn paypal-btn">
            <i class="fab fa-paypal"></i> PayPal
          </button>
          <button id="cardBtn" class="payment-btn card-btn">
            <i class="fas fa-credit-card"></i> Tarjeta
          </button>
          <button id="clearCart" class="payment-btn clear-btn">
            <i class="fas fa-trash"></i> Vaciar Carrito
          </button>
        </div>
        <div id="paypal-button-container" class="paypal-container"></div>
        <div id="paypal-error-message" class="paypal-error" style="display: none;"></div>
      </div>
    </div>
  </div>

  <!-- MODAL YAPE -->
  <div id="yapeModal" class="yape-modal" aria-hidden="true">
    <div class="yape-modal-content">
      <header class="yape-modal-header">
        <h3>Pagar con Yape</h3>
        <button id="closeYapeModal" class="yape-close-btn">
          <i class="fas fa-times"></i>
        </button>
      </header>
      <div class="yape-modal-body">
        <div class="yape-total-section">
          <p class="yape-label">Total a Pagar:</p>
          <p class="yape-amount">S/ <span id="yapeTotal">0.00</span></p>
        </div>
        <div class="yape-instructions">
          <h4>📱 Instrucciones de Pago:</h4>
          <ol>
            <li>Abre tu app Yape</li>
            <li>Escanea el código QR o transfiere al número</li>
            <li>Confirma el pago al finalizar</li>
          </ol>
        </div>
        <div class="yape-qr-section">
          <div class="yape-qr-container">
            <img id="yapeQRImage" src="imagenes/WhatsApp Image 2025-11-08 at 7.48.48 PM.jpeg" alt="Código QR Yape" class="yape-qr">
          </div>
        </div>
        <div class="yape-phone-section">
          <p class="yape-label">Número Yape:</p>
          <div class="yape-phone-display">
            <span id="yapePhone">988469378</span>
            <button id="copyYapePhone" class="copy-btn" title="Copiar número">
              <i class="fas fa-copy"></i>
            </button>
          </div>
        </div>
        <div class="yape-confirm-section">
          <p class="yape-label">¿Ya realizaste el pago?</p>
          <button id="confirmYapePayment" class="yape-confirm-btn">
            ✅ Confirmar Pago
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="main.js"></script>
</body>
</html>
