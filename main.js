/* main.js — Lógica del catálogo y carrito (sin backend).
   Guarda carrito en localStorage para persistencia local. */

/* ====== Datos de ejemplo ======
   Ajusta / añade productos reales (id, nombre, precio, categoría, imagen)
*/
const PRODUCTS = [
  { id: "p1", title: "Arroz 49kg", price: 110.00, category: "alimentos", image: "imagenes/Arroz.webp", desc: "Arroz de calidad" },
  { id: "p2", title: "Aceite 20L", price: 35.00, category: "alimentos", image: "imagenes/aceite.webp", desc: "Aceite comestible" },
  { id: "p3", title: "Pack de Ollas", price: 150.00, category: "hogar", image: "imagenes/ollas.webp", desc: "Juego de ollas" },
  { id: "p4", title: "Detergente", price: 8.50, category: "higiene", image: "imagenes/limpieza.png", desc: "Detergente para ropa" },
  { id: "p5", title: "Pan fresco", price: 3.50, category: "alimentos", image: "imagenes/pan.jpeg", desc: "Pan del día" },
  { id: "p6", title: "Desinfectante", price: 4.00, category: "higiene", image: "imagenes/clorox.webp", desc: "Desinfectante liquido" },
  { id: "p7", title: "Detergente", price: 5.00, category: "higiene", image: "imagenes/ace.webp", desc: "Detergente en polvo Ace Floral" },
  { id: "p8", title: "Lavadora", price: 550.00, category: "hogar", image: "imagenes/lavadora.jpg", desc: "Lavadora inteligente" },
  { id: "p9", title: "Licuadora + Olla Arrocera", price: 160.00, category: "hogar", image: "imagenes/imaco.jpg", desc: "Combo licuadora + olla arrocera" }
];

/* ====== Utilities ====== */
const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => Array.from(document.querySelectorAll(sel));

/* ====== Estado del carrito ====== */
let cart = JSON.parse(localStorage.getItem("jsms_cart") || "[]");

/* ====== Elementos DOM ====== */
const productGrid = $("#product-grid");
const cartCount = $("#cart-count");
const cartBtn = $("#cartBtn");
const cartModal = $("#cartModal");
const cartItemsEl = $("#cartItems");
const cartTotalEl = $("#cartTotal");
const closeCart = $("#closeCart");
const clearCartBtn = $("#clearCart");
const filters = $$(".filter-btn");
const menuToggle = $("#menuToggle");
const nav = $("#nav");
const yearEl = $("#year");
const paypalButtonContainer = $("#paypal-button-container");
const yapeBtn = $("#yapeBtn");
const yapeModal = $("#yapeModal");
const closeYapeModal = $("#closeYapeModal");
const yapeTotal = $("#yapeTotal");
const yapePhone = $("#yapePhone");
const copyYapePhone = $("#copyYapePhone");
const confirmYapePayment = $("#confirmYapePayment");

/* ====== Inicialización ====== */
document.addEventListener("DOMContentLoaded", () => {
  yearEl.textContent = new Date().getFullYear();
  renderProducts(PRODUCTS);
  updateCartUI();
  setupEventListeners();
});

/* ====== Render productos dinámicamente ====== */
function renderProducts(items){
  productGrid.innerHTML = "";
  items.forEach(p => {
    const card = document.createElement("article");
    card.className = "product-card";
    card.innerHTML = `
      <img src="${p.image}" alt="${p.title}">
      <h3>${p.title}</h3>
      <p>${p.desc}</p>
      <div class="price">S/ ${p.price.toFixed(2)}</div>
      <div class="actions">
        <button class="btn small add-to-cart" data-id="${p.id}"><i class="fas fa-cart-plus"></i> Agregar</button>
        <button class="btn ghost small view-more" data-id="${p.id}"><i class="fas fa-info-circle"></i> Ver</button>
      </div>
    `;
    productGrid.appendChild(card);
  });
}

/* ====== Event listeners globales ====== */
function setupEventListeners(){
  // Delegación: agregar al carrito
  productGrid.addEventListener("click", (e) => {
    const addBtn = e.target.closest(".add-to-cart");
    if(addBtn){
      const id = addBtn.dataset.id;
      addToCart(id);
    }
    const viewBtn = e.target.closest(".view-more");
    if(viewBtn){
      const id = viewBtn.dataset.id;
      const p = PRODUCTS.find(x=>x.id===id);
      alert(`${p.title}\n\n${p.desc}\n\nPrecio: S/ ${p.price.toFixed(2)}`);
    }
  });

  // Filtros
  filters.forEach(btn => {
    btn.addEventListener("click", () => {
      filters.forEach(b=>b.classList.remove("active"));
      btn.classList.add("active");
      const filter = btn.dataset.filter;
      if(filter === "all") renderProducts(PRODUCTS);
      else renderProducts(PRODUCTS.filter(p => p.category === filter));
    });
  });

  // Carrito: abrir / cerrar
  cartBtn.addEventListener("click", () => toggleCart(true));
  closeCart.addEventListener("click", () => toggleCart(false));
  cartModal.addEventListener("click", (e) => {
    if(e.target === cartModal) toggleCart(false);
  });

  // Vaciar carrito
  clearCartBtn.addEventListener("click", () => {
    if(confirm("¿Vaciar el carrito?")) {
      cart = []; 
      persistCart(); 
      updateCartUI();
      destroyPayPalButtons();
    }
  });

  // Yape: Abrir modal
  yapeBtn.addEventListener("click", () => {
    if(cart.length === 0){
      alert("Tu carrito está vacío");
      return;
    }
    openYapeModal();
  });

  // Yape: Cerrar modal
  closeYapeModal.addEventListener("click", () => closeYapeModalFunc());
  yapeModal.addEventListener("click", (e) => {
    if(e.target === yapeModal) closeYapeModalFunc();
  });

  // Yape: Copiar número de teléfono
  copyYapePhone.addEventListener("click", () => {
    const phoneNumber = yapePhone.textContent.trim();
    navigator.clipboard.writeText(phoneNumber).then(() => {
      copyYapePhone.innerHTML = '<i class="fas fa-check"></i>';
      copyYapePhone.style.background = '#28a745';
      setTimeout(() => {
        copyYapePhone.innerHTML = '<i class="fas fa-copy"></i>';
        copyYapePhone.style.background = '';
      }, 2000);
    }).catch(err => {
      console.error('Error al copiar:', err);
      alert('Número: ' + phoneNumber);
    });
  });

  // Yape: Confirmar pago
  confirmYapePayment.addEventListener("click", () => {
    if(confirm("¿Confirmas que ya realizaste el pago con Yape?\n\nTotal: S/ " + totalCart().toFixed(2))) {
      // Simular confirmación de pago
      alert('¡Pago confirmado!\n\nGracias por tu compra. Te contactaremos pronto para confirmar tu pedido.');
      
      // Limpiar carrito
      cart = [];
      persistCart();
      updateCartUI();
      toggleCart(false);
      closeYapeModalFunc();
    }
  });

  // Delegación dentro del carrito (aumentar / disminuir / eliminar)
  cartItemsEl.addEventListener("click", (e) => {
    const removeBtn = e.target.closest(".remove-item");
    if(removeBtn){
      const id = removeBtn.dataset.id;
      cart = cart.filter(i => i.id !== id);
      persistCart(); 
      updateCartUI();
      return;
    }
    const incBtn = e.target.closest(".inc");
    const decBtn = e.target.closest(".dec");
    if(incBtn || decBtn){
      const id = (incBtn || decBtn).dataset.id;
      cart = cart.map(item => {
        if(item.id === id){
          const delta = incBtn ? 1 : -1;
          item.qty = Math.max(1, item.qty + delta);
        }
        return item;
      });
      persistCart(); 
      updateCartUI();
    }
  });

  // Formulario contacto: validación y mensaje simulado
  const contactForm = $("#contactForm");
  const formMsg = $("#formMsg");
  contactForm.addEventListener("submit", (e) => {
    e.preventDefault();
    if(!contactForm.checkValidity()){
      formMsg.style.color = "crimson";
      formMsg.textContent = "Por favor completa todos los campos correctamente.";
      return;
    }
    const data = {
      name: contactForm.name.value.trim(),
      email: contactForm.email.value.trim(),
      phone: contactForm.phone.value.trim(),
      message: contactForm.message.value.trim()
    };
    // Simular envío
    formMsg.style.color = "green";
    formMsg.textContent = "Mensaje enviado. ¡Gracias, " + data.name + "!";
    contactForm.reset();
    setTimeout(()=> formMsg.textContent = "", 5000);
  });

  // Toggle menú en móvil
  menuToggle.addEventListener("click", () => {
    nav.classList.toggle("active");
  });

  // Cerrar menú al hacer clic en enlace
  $$(".nav a").forEach(a => a.addEventListener("click", ()=> nav.classList.remove("active")));
}

/* ====== Carrito — lógica ====== */
function addToCart(id){
  const product = PRODUCTS.find(p => p.id === id);
  if(!product) return;
  const exists = cart.find(i => i.id === id);
  if(exists) exists.qty++;
  else cart.push({ id: product.id, title: product.title, price: product.price, image: product.image, qty: 1});
  persistCart();
  updateCartUI();
  // Pequeña confirmación visual
  const btn = document.querySelector(`.add-to-cart[data-id="${id}"]`);
  if(btn){
    btn.innerHTML = `<i class="fas fa-check"></i> Agregado`;
    setTimeout(()=> btn.innerHTML = `<i class="fas fa-cart-plus"></i> Agregar`, 900);
  }
}

function persistCart(){ localStorage.setItem("jsms_cart", JSON.stringify(cart)); }

function totalCart(){ return cart.reduce((s,item) => s + item.price * item.qty, 0); }

function updateCartUI(){
  // Contador
  const totalQty = cart.reduce((s,i)=> s + i.qty, 0);
  cartCount.textContent = totalQty;

  // Items en modal
  cartItemsEl.innerHTML = "";
  if(cart.length === 0){
    cartItemsEl.innerHTML = `<div style="padding:8px;color:var(--muted)">Tu carrito está vacío.</div>`;
    cartTotalEl.textContent = "0.00";
    destroyPayPalButtons();
    return;
  }

  cart.forEach(item => {
    const div = document.createElement("div");
    div.className = "cart-item";
    div.innerHTML = `
      <img src="${item.image}" alt="${item.title}">
      <div class="meta">
        <h4>${item.title}</h4>
        <small>S/ ${item.price.toFixed(2)}</small>
        <div class="qty" style="margin-top:8px">
          <button class="btn small dec" data-id="${item.id}">-</button>
          <span style="padding:6px 8px;min-width:36px;display:inline-block;text-align:center">${item.qty}</span>
          <button class="btn small inc" data-id="${item.id}">+</button>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
        <div style="font-weight:800">S/ ${(item.price * item.qty).toFixed(2)}</div>
        <button class="btn ghost small remove-item" data-id="${item.id}">Eliminar</button>
      </div>
    `;
    cartItemsEl.appendChild(div);
  });

  cartTotalEl.textContent = totalCart().toFixed(2);
  
  // Inicializar PayPal con los productos del carrito
  initPayPalButtons();
}

/* ====== Mostrar/ocultar carrito ====== */
function toggleCart(show){
  if(show){
    cartModal.classList.add("show");
    cartModal.setAttribute("aria-hidden","false");
    updateCartUI();
  } else {
    cartModal.classList.remove("show");
    cartModal.setAttribute("aria-hidden","true");
  }
}

/* ====== Funciones Yape ====== */
function openYapeModal(){
  const total = totalCart().toFixed(2);
  yapeTotal.textContent = total;
  yapeModal.classList.add("show");
  yapeModal.setAttribute("aria-hidden","false");
  // Verificar que la imagen del QR esté cargada
  generateYapeQR();
}

function closeYapeModalFunc(){
  yapeModal.classList.remove("show");
  yapeModal.setAttribute("aria-hidden","true");
}

function generateYapeQR(){
  // Ya no necesitamos generar el QR dinámicamente
  // La imagen JPEG del QR se muestra directamente en el HTML
  // Solo verificamos que la imagen esté cargada correctamente
  const qrImage = document.getElementById('yapeQRImage');
  if(qrImage){
    // Manejar error si la imagen no se carga
    qrImage.onerror = function() {
      console.error('Error al cargar la imagen del QR de Yape');
      this.style.display = 'none';
      const qrContainer = document.getElementById('yapeQR');
      if(qrContainer){
        qrContainer.innerHTML = `
          <div style="width: 250px; height: 250px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 10px; margin: 0 auto; border: 2px dashed #ccc;">
            <p style="color: #999; text-align: center; padding: 20px;">
              Error al cargar QR<br/>
              Por favor, verifica que la imagen<br/>
              <strong>yape-qr.jpg</strong> esté en la carpeta<br/>
              <strong>imagenes/</strong>
            </p>
          </div>
        `;
      }
    };
    
    // Confirmar que la imagen se cargó correctamente
    qrImage.onload = function() {
      console.log('Imagen del QR de Yape cargada correctamente');
    };
  }
}

/* ====== Integración PayPal con Carrito ====== */
let paypalButtonsInstance = null;

function destroyPayPalButtons(){
  if(paypalButtonContainer){
    paypalButtonContainer.innerHTML = "";
  }
  paypalButtonsInstance = null;
}

function initPayPalButtons(){
  // Destruir botones anteriores
  destroyPayPalButtons();
  
  // Verificar que hay productos en el carrito
  if(cart.length === 0){
    return;
  }
  
  // Verificar que PayPal SDK está cargado
  if(typeof paypal === 'undefined'){
    console.warn('PayPal SDK no está cargado, reintentando...');
    if(paypalButtonContainer){
      paypalButtonContainer.innerHTML = '<p style="padding: 10px; color: #666; text-align: center;">Cargando PayPal...</p>';
    }
    setTimeout(initPayPalButtons, 500);
    return;
  }
  
  // Calcular total de items para validar
  let itemsTotal = 0;
  const items = cart.map(item => {
    const itemTotal = parseFloat((item.price * item.qty).toFixed(2));
    itemsTotal += itemTotal;
    return {
      name: item.title.substring(0, 127), // PayPal limita a 127 caracteres
      quantity: item.qty.toString(),
      unit_amount: {
        currency_code: 'PEN',
        value: parseFloat(item.price).toFixed(2)
      }
    };
  });
  
  // El total debe coincidir exactamente con la suma de items
  const total = itemsTotal.toFixed(2);
  
  // Validar que el total sea mayor a 0
  if(parseFloat(total) <= 0){
    if(paypalButtonContainer){
      paypalButtonContainer.innerHTML = '<p style="color: #d32f2f; padding: 10px; background: #ffebee; border-radius: 8px; text-align: center;">⚠️ Error: El total debe ser mayor a 0</p>';
    }
    return;
  }
  
  // Renderizar botones de PayPal
  try {
    paypalButtonsInstance = paypal.Buttons({
      createOrder: function(data, actions) {
        console.log('Creando orden de PayPal:', { items, total });
        return actions.order.create({
          purchase_units: [{
            description: 'Compra en JSMS - Artículos de Primera Necesidad',
            amount: {
              currency_code: 'PEN',
              value: total,
              breakdown: {
                item_total: {
                  currency_code: 'PEN',
                  value: total
                }
              }
            },
            items: items
          }],
          application_context: {
            brand_name: 'JSMS',
            landing_page: 'BILLING',
            user_action: 'PAY_NOW'
          }
        }).catch(function(err) {
          console.error('Error al crear la orden:', err);
          throw err;
        });
      },
      onApprove: function(data, actions) {
        console.log('Orden aprobada:', data);
        return actions.order.capture().then(function(details) {
          // Pago exitoso
          console.log('Pago completado:', details);
          alert('¡Pago realizado con éxito!\n\nID de transacción: ' + details.id + '\nTotal: S/ ' + total + '\n\nGracias por tu compra.');
          
          // Limpiar carrito después del pago exitoso
          cart = [];
          persistCart();
          updateCartUI();
          toggleCart(false);
        }).catch(function(err) {
          console.error('Error al capturar el pago:', err);
          let errorMsg = 'Hubo un error al procesar el pago.';
          if(err.message){
            errorMsg += '\n\nDetalle: ' + err.message;
          }
          alert(errorMsg + '\n\nPor favor, contacta con soporte.');
        });
      },
      onError: function(err) {
        console.error('Error en PayPal:', err);
        let errorMsg = 'Hubo un error al procesar el pago.';
        if(err.message){
          errorMsg += '\n\n' + err.message;
        }
        if(err.message && err.message.includes('client-id')){
          errorMsg = '⚠️ ERROR: El Client ID de PayPal no es válido.\n\nPor favor, reemplaza "test" con tu Client ID real de PayPal en la línea 9 de index.php';
        }
        alert(errorMsg);
      },
      onCancel: function(data) {
        console.log('Pago cancelado por el usuario:', data);
      },
      style: {
        layout: 'vertical',
        color: 'blue',
        shape: 'rect',
        label: 'paypal'
      }
    });
    
    if(paypalButtonContainer){
      paypalButtonsInstance.render('#paypal-button-container').catch(err => {
        console.error('Error al renderizar botones de PayPal:', err);
        let errorMsg = '⚠️ Error al cargar los botones de PayPal.';
        if(err.message && err.message.includes('client-id')){
          errorMsg = '⚠️ ERROR: El Client ID de PayPal no es válido.\n\nEl valor "test" no funciona. Necesitas un Client ID real de PayPal.\n\n1. Ve a https://developer.paypal.com/\n2. Crea una aplicación\n3. Obtén tu Client ID\n4. Reemplaza "test" en la línea 9 de index.php';
        } else if(err.message){
          errorMsg += '\n\n' + err.message;
        }
        paypalButtonContainer.innerHTML = '<div style="color: #d32f2f; padding: 15px; background: #ffebee; border-radius: 8px; text-align: center; line-height: 1.6;">' + errorMsg + '</div>';
      });
    }
  } catch(error) {
    console.error('Error al crear botones de PayPal:', error);
    if(paypalButtonContainer){
      let errorMsg = '⚠️ Error al inicializar PayPal.';
      if(error.message){
        errorMsg += '<br><br>' + error.message;
      }
      if(error.message && error.message.includes('client-id')){
        errorMsg = '⚠️ ERROR: El Client ID de PayPal no es válido.<br><br>Reemplaza "test" con tu Client ID real en index.php línea 9';
      }
      paypalButtonContainer.innerHTML = '<div style="color: #d32f2f; padding: 15px; background: #ffebee; border-radius: 8px; text-align: center;">' + errorMsg + '</div>';
    }
  }
}
/*Script: movible y guarda posición*/
const btn = document.getElementById("whatsapp-btn");
let offsetX, offsetY, isDragging = false;

// Cargar posición guardada
window.addEventListener("load", () => {
  const savedPos = JSON.parse(localStorage.getItem("whatsappPosition"));
  if (savedPos) {
    btn.style.left = savedPos.left;
    btn.style.top = savedPos.top;
    btn.style.right = "auto";
    btn.style.bottom = "auto";
  }
  // Pequeño rebote inicial
  setTimeout(() => {
    btn.classList.remove("bounce");
    void btn.offsetWidth; // Reinicia la animación
    btn.classList.add("bounce");
  }, 300);
});

// Iniciar arrastre
btn.addEventListener("mousedown", startDrag);
btn.addEventListener("touchstart", startDrag);

function startDrag(e) {
  isDragging = true;
  const event = e.type === "touchstart" ? e.touches[0] : e;
  offsetX = event.clientX - btn.getBoundingClientRect().left;
  offsetY = event.clientY - btn.getBoundingClientRect().top;
  document.addEventListener("mousemove", onDrag);
  document.addEventListener("mouseup", stopDrag);
  document.addEventListener("touchmove", onDrag);
  document.addEventListener("touchend", stopDrag);
}

// Mover el botón
function onDrag(e) {
  if (!isDragging) return;
  const event = e.type === "touchmove" ? e.touches[0] : e;
  const x = event.clientX - offsetX;
  const y = event.clientY - offsetY;
  btn.style.left = x + "px";
  btn.style.top = y + "px";
  btn.style.right = "auto";
  btn.style.bottom = "auto";
}

// Detener arrastre y guardar posición
function stopDrag() {
  isDragging = false;
  const position = {
    left: btn.style.left,
    top: btn.style.top
  };
  localStorage.setItem("whatsappPosition", JSON.stringify(position));
  document.removeEventListener("mousemove", onDrag);
  document.removeEventListener("mouseup", stopDrag);
  document.removeEventListener("touchmove", onDrag);
  document.removeEventListener("touchend", stopDrag);
}