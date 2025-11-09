
<nav class="side-menu">
    <ul class="side-menu-list p-0">
        <li class="red" >
            <a href="inicio.php" class="activo">
                <img src="../public/img-inicio/house.png" class="img-inicio" alt="">
                <span class="lbl">INICIO</span>
            </a>
        </li>

        <li class="red" >
            <a href="usuario.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">USUARIO</span>
            </a>
        </li>

        <li class="red" >
            <a href="unidad_minera.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">UNIDADES MINERAS</span>
            </a>
        </li>

        <li class="red" >
            <a href="conductores.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">CONDUCTORES</span>
            </a>
        </li>

        <li class="red" >
            <a href="vehiculos.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">VEHICULOS</span>
            </a>
        </li>

        <li class="red" >
            <a href="asignar_vehiculos.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">ASIGNAR VEHICULOS</span>
            </a>
        </li>

        <li class="red" >
            <a href="herramientas.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">HERRAMIENTAS</span>
            </a>
        </li>

        <li class="red" >
            <a href="suministros.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">SUMINISTROS</span>
            </a>
        </li>

        <li class="red" >
            <a href="certificados.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">CERTIFICADOS</span>
            </a>
        </li>

        <li class="red" >
            <a href="mantenimiento.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">MANTENIMIENTO</span>
            </a>
        </li>

        <li class="red" >
            <a href="kilometrajes.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">REGISTRO KILOMETRAJE</span>
            </a>
        </li>

        <li class="red" >
            <a href="reportes.php" class="activo">
                <img src="../public/img-inicio/team.png" class="img-inicio" alt="">
                <span class="lbl">REPORTES</span>
            </a>
        </li>




    </ul>
</nav>

<style>
    /* Subítems ocultos por defecto */
    .side-menu-sublist {
        display: none;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    /* Subítems alineados a la izquierda con margen */
    .side-menu-sublist .subitem a {
        display: block;
        padding-left: 50px;
        /* Indentación izquierda */
        font-size: 16px;
        color: #333;
        text-decoration: none;
    }

    /* Hover efecto */
    .side-menu-sublist .subitem a:hover {
        background: rgba(11, 150, 214, 0.1);
    }

    /* Flecha a la derecha del texto principal */
    .toggle-submenu {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Icono de la flecha */
    .toggle-submenu .arrow {
        font-size: 12px;
        margin-left: auto;
        transition: transform 0.3s;
    }

    /* Girar flecha cuando está abierto */
    .asignar-menu.open .arrow {
        transform: rotate(180deg);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.toggle-submenu');
        toggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const parent = this.parentElement;
                const submenu = this.nextElementSibling;
                if (submenu.style.display === 'block') {
                    submenu.style.display = 'none';
                    parent.classList.remove('open');
                } else {
                    submenu.style.display = 'block';
                    parent.classList.add('open');
                }
            });
        });
    });
</script>