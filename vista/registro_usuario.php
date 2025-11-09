<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
  header('location:login/login.php');
  exit;
}

// Validar acceso de conductor: solo pueden ver kilometrajes
include "../modelo/validar_conductor.php";

include "../modelo/conexion.php";
include "../controlador/controlador_registrar_usuario.php"; // PRG: antes de HTML

/* Flash */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
  ul li:nth-child(2) .activo {
    background: #0b96d6 !important;
  }

  h4.text-center.text-secondary {
    color: #374151 !important;
    font-weight: 800;
    letter-spacing: .3px;
  }

  .card-like {
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
    margin-top: 14px;
  }

  .form-text {
    color: #6b7280;
    font-size: .88rem;
  }

  .card-like .form-control,
  .card-like select.form-control {
    width: 100%;
  }

  .actions-row {
    display: block;
    width: 100%;
    clear: both;
    margin-top: 10px;
  }

  .actions-right {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    float: none !important;
    position: static !important;
  }

  .actions-right .btn+.btn {
    margin-left: 6px;
  }

  @media (max-width:575.98px) {
    .actions-right {
      flex-direction: column;
    }

    .actions-right .btn {
      width: 100%;
    }
  }

  .password-wrapper {
    position: relative;
  }

  .toggle-pass {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    border: 0;
    background: transparent;
    padding: 4px 6px;
    cursor: pointer;
    color: #6b7280;
  }

  .form-error-summary {
    display: none;
    border: 1px solid #f5c6cb;
    background: #f8d7da;
    color: #721c24;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 12px;
    font-size: .95rem;
  }
</style>

<div class="page-content">
  <h4 class="text-center text-secondary">REGISTRO DE USUARIOS</h4>

  <div class="card-like">
    <form action="" method="POST" autocomplete="off" class="w-100 js-validate" novalidate>
      <div class="form-error-summary">Por favor, complete todos los campos obligatorios antes de registrar.</div>

      <!-- Fila 1 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtnombre" class="form-label">Nombre</label>
          <input type="text" id="txtnombre" name="txtnombre" class="form-control" placeholder="Ej: Carlos Alberto" maxlength="80" required>
          <small class="form-text">Nombres del usuario.</small>
          <div class="invalid-feedback">Ingrese el nombre.</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtapellidos" class="form-label">Apellidos</label>
          <input type="text" id="txtapellidos" name="txtapellidos" class="form-control" placeholder="Ej: Pérez Humán" maxlength="120" required>
          <small class="form-text">Apellidos paterno y materno.</small>
          <div class="invalid-feedback">Ingrese los apellidos.</div>
        </div>
      </div>

      <!-- Fila 2 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtdni" class="form-label">DNI</label>
          <input type="text" id="txtdni" name="txtdni" class="form-control" placeholder="8 dígitos" inputmode="numeric" maxlength="8" required>
          <small class="form-text">Solo números, exactamente 8 dígitos.</small>
          <div class="invalid-feedback">Ingrese un DNI válido de 8 dígitos.</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtusuario" class="form-label">Usuario</label>
          <input type="text" id="txtusuario" name="txtusuario" class="form-control" placeholder="Ej: cperez" minlength="3" maxlength="30" pattern="^[^\s]+$" title="Sin espacios. Mínimo 3 caracteres." required>
          <small class="form-text">Nombre de inicio de sesión (sin espacios).</small>
          <div class="invalid-feedback">Ingrese un usuario válido (mín. 3 caracteres, sin espacios).</div>
        </div>
      </div>

      <!-- Fila 3 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtpassword" class="form-label">Contraseña</label>
          <div class="password-wrapper">
            <input type="password" id="txtpassword" name="txtpassword" class="form-control" placeholder="Mínimo 6 caracteres" minlength="6" maxlength="64" autocomplete="new-password" required>
            <button type="button" class="toggle-pass" aria-label="Mostrar/ocultar contraseña" onclick="togglePassword()"><i class="fa fa-eye"></i></button>
          </div>
          <small class="form-text">Usa letras y números. Mínimo 6 caracteres.</small>
          <div class="invalid-feedback">Ingrese una contraseña válida (mínimo 6 caracteres).</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtrol" class="form-label">Rol</label>
          <select id="txtrol" name="txtrol" class="form-control" required>
            <option value="">-- Seleccione un rol --</option>
            <option value="Administrador">Administrador</option>
            <option value="Asistente de flota">Asistente de flota</option>
            <option value="Conductor">Conductor</option>
          </select>
          <small class="form-text">Permisos y alcance dentro del sistema.</small>
          <div class="invalid-feedback">Seleccione un rol.</div>
        </div>
      </div>

      <!-- Fila 4 -->
      <div class="actions-row">
        <div class="actions-right">
          <a href="usuario.php" class="btn btn-secondary btn-rounded">Atrás</a>
          <button type="submit" class="btn btn-primary btn-rounded" value="ok" name="btnregistrar">Registrar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require('./layout/footer.php'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  // DNI: solo números, 8 dígitos
  (function() {
    const dni = document.getElementById('txtdni');
    if (!dni) return;

    function sanitize() {
      dni.value = dni.value.replace(/\D/g, '').slice(0, 8);
    }
    ['input', 'paste', 'blur', 'keyup'].forEach(evt => dni.addEventListener(evt, () => setTimeout(sanitize, 0)));
  })();

  function togglePassword() {
    const input = document.getElementById('txtpassword');
    const icon = event.currentTarget.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  }

  // Validación simple
  (function() {
    function validateForm(form) {
      let ok = true;
      form.querySelectorAll('[required]').forEach(el => {
        let valid = (el.value || '').trim() !== '';
        if (el.type === 'email' && el.value) valid = el.checkValidity();
        if (el.id === 'txtdni' && el.value) valid = /^\d{8}$/.test(el.value);
        if (el.id === 'txtpassword' && el.value) valid = el.value.length >= 6;
        if (!valid) {
          el.classList.add('is-invalid');
          ok = false;
        } else {
          el.classList.remove('is-invalid');
        }
      });
      const sum = form.querySelector('.form-error-summary');
      if (sum) sum.style.display = ok ? 'none' : 'block';
      return ok;
    }
    document.addEventListener('submit', function(e) {
      const f = e.target.closest('form.js-validate');
      if (!f) return;
      if (!validateForm(f)) {
        e.preventDefault();
        (f.querySelector('.is-invalid') || {}).focus?.();
      }
    });
    document.addEventListener('input', function(e) {
      const el = e.target;
      if (el.hasAttribute('required') && el.value.trim() !== '') {
        el.classList.remove('is-invalid');
      }
    });
  })();
</script>

<?php if (!empty($flash)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: '<?= $flash['tipo'] ?>',
        title: '<?= $flash['titulo'] ?? 'Listo' ?>',
        text: '<?= $flash['mensaje'] ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
      });
    });
  </script>
<?php endif; ?>