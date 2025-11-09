<?php
session_start();
if (empty($_SESSION['id'])) {
  header('location:login/login.php');
  exit;
}

/* ========= CONEXIÓN PDO ========= */
try {
  $pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=sis_vpice;charset=utf8mb4',
    'root',
    '',
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (Exception $e) {
  die("Error de conexión: " . $e->getMessage());
}

/* ========= HELPERS ========= */
function one($pdo, $sql, $params = [])
{
  $st = $pdo->prepare($sql);
  $st->execute($params);
  $v = $st->fetchColumn();
  return $v !== false ? $v : 0;
}
function allRows($pdo, $sql, $params = [])
{
  $st = $pdo->prepare($sql);
  $st->execute($params);
  return $st->fetchAll();
}

/* ========= FECHAS ========= */
$hoy    = date('Y-m-d');
$en15   = date('Y-m-d', strtotime('+15 days'));
$hace30 = date('Y-m-d', strtotime('-30 days'));

/* ========= KPIs ========= */
$kpi_activos = one($pdo, "SELECT COUNT(*) FROM habilitacion_personal WHERE estado='ACTIVO'");
$kpi_cesados = one($pdo, "SELECT COUNT(*) FROM habilitacion_personal WHERE estado='CESADO'");
$kpi_post_30 = one($pdo, "SELECT COUNT(*) FROM postulante WHERE fecha_postulacion >= ?", [$hace30]);

$kpi_activo_30 = one($pdo, "SELECT COUNT(*) FROM habilitacion_personal WHERE DATE(fecha_registro) >= ?", [$hace30]);
$kpi_cesado_30 = one($pdo, "SELECT COUNT(*) FROM habilitacion_personal WHERE estado='CESADO' AND DATE(fecha_cese) >= ?", [$hace30]);

$kpi_contratos_vigentes    = one($pdo, "SELECT COUNT(*) FROM contratos WHERE fecha_inicio <= ? AND fecha_fin >= ?", [$hoy, $hoy]);
$kpi_contratos_por_vencer  = one($pdo, "SELECT COUNT(*) FROM contratos WHERE fecha_fin BETWEEN ? AND ?", [$hoy, $en15]);

function riskClass($n)
{
  if ($n >= 10) return 'kpi-danger';
  if ($n >= 5)  return 'kpi-warning';
  return 'kpi-success';
}
$kpi_vencer_class = riskClass($kpi_contratos_por_vencer);

/* ========= GRÁFICOS ========= */
// Altas/Bajas últimas 4 semanas
$altas_bajas = allRows($pdo, "
  WITH RECURSIVE semanas AS (
    SELECT 0 AS w UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
  )
  SELECT 
    DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL w WEEK), '%Y-%u') AS semana_iso,
    SUM(CASE WHEN DATE(hp.fecha_registro) BETWEEN DATE_SUB(CURDATE(), INTERVAL (w+1) WEEK) + INTERVAL 1 DAY AND DATE_SUB(CURDATE(), INTERVAL w WEEK) THEN 1 ELSE 0 END) AS altas,
    SUM(CASE WHEN hp.estado='CESADO' AND DATE(hp.fecha_cese) BETWEEN DATE_SUB(CURDATE(), INTERVAL (w+1) WEEK) + INTERVAL 1 DAY AND DATE_SUB(CURDATE(), INTERVAL w WEEK) THEN 1 ELSE 0 END) AS bajas
  FROM semanas s
  CROSS JOIN habilitacion_personal hp
  GROUP BY 1
  ORDER BY 1 ASC
");

// Cursos últimos 30 días
$cursos_resumen = allRows($pdo, "
  SELECT resultado, COUNT(*) total
  FROM resultado_cursos_postulante
  WHERE fecha_resultado >= ?
  GROUP BY resultado
", [$hace30]);

/* ========= TABLAS ========= */
$contratos_vencer = allRows($pdo, "
  SELECT c.idcontrato, p.nombres, p.apellidos, pr.nombre_proyecto, c.cargo, c.fecha_fin
  FROM contratos c
  JOIN habilitacion_personal hp ON hp.idhabilitacion_personal = c.idhabilitacion_personal
  JOIN postulante po ON po.id_postulante = hp.id_postulante
  JOIN persona p ON p.id_persona = po.id_persona
  JOIN proyectos pr ON pr.id_proyectos = c.id_proyectos
  WHERE c.fecha_fin BETWEEN ? AND ?
  ORDER BY c.fecha_fin ASC
", [$hoy, $en15]);

$examenes = allRows($pdo, "
  SELECT remp.idresultado_examenmedico_postulante, p.nombres, p.apellidos, remp.fecha_resultado, remp.resultado, remp.observaciones
  FROM resultado_examenmedico_postulante remp
  JOIN postulante_examenmedico pem ON pem.id_postulante_examenmedico = remp.id_postulante_examenmedico
  JOIN postulante po ON po.id_postulante = pem.id_postulante
  JOIN persona p ON p.id_persona = po.id_persona
  WHERE remp.fecha_resultado >= ?
  ORDER BY remp.fecha_resultado DESC, remp.idresultado_examenmedico_postulante DESC
", [$hace30]);

$postulantes_recientes = allRows($pdo, "
  SELECT po.id_postulante, p.nombres, p.apellidos, po.fecha_postulacion, pr.nombre_proyecto, po.puesto_postulado, po.estado
  FROM postulante po
  JOIN persona p ON p.id_persona = po.id_persona
  JOIN proyectos pr ON pr.id_proyectos = po.id_proyectos
  ORDER BY po.fecha_postulacion DESC, po.id_postulante DESC
  LIMIT 10
");
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
  /* Menú activo */
  ul li:nth-child(1) .activo {
    background: #0b96d6 !important;
  }

  /* Titular */
  h4.text-secondary {
    color: #374151 !important;
    font-weight: 800;
    letter-spacing: .3px;
  }

  /* Card contenedor */
  .card-like {
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
    margin-top: 14px;
  }

  /* Grid */
  .grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 16px;
  }

  @media (max-width:1024px) {
    .grid {
      grid-template-columns: repeat(6, 1fr);
    }
  }

  @media (max-width:640px) {
    .grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  /* Tarjeta simple (contenedor de gráfico/tabla) */
  .card {
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
    padding: 16px;
  }

  /* Encabezados de tabla (azul, igual a listas) */
  .table thead th,
  .thead-dark th,
  .modal .table thead th {
    background: #0b96d6 !important;
    color: #fff !important;
    border-color: #0b86c0 !important;
  }

  /* ===== KPIs ===== */
  :root {
    --c-primary: #0b96d6;
    --c-success: #10b981;
    --c-warning: #f59e0b;
    --c-danger: #ef4444;
    --c-neutral: #6b7280;
  }

  .kpi-card {
    border-radius: 14px;
    padding: 18px;
    background: #fff;
    border: 1px solid #eef2f7;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .06);
    position: relative;
    overflow: hidden;
    border-left: 4px solid transparent;
  }

  .kpi-title {
    font-size: .85rem;
    color: var(--c-neutral);
    margin: 0 0 6px;
    font-weight: 600;
    letter-spacing: .3px;
  }

  .kpi-value {
    font-size: 1.7rem;
    font-weight: 800;
    margin: 0;
  }

  .kpi-primary {
    border-left-color: var(--c-primary);
  }

  .kpi-success {
    border-left-color: var(--c-success);
  }

  .kpi-warning {
    border-left-color: var(--c-warning);
  }

  .kpi-danger {
    border-left-color: var(--c-danger);
  }

  .kpi-icon {
    position: absolute;
    right: 12px;
    top: 12px;
    opacity: .12;
    width: 48px;
    height: 48px;
    color: #000;
  }

  .mini-meter {
    height: 8px;
    width: 100%;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
  }

  .mini-meter>span {
    display: block;
    height: 100%;
    background: var(--c-success);
  }

  canvas {
    max-width: 100%;
  }
</style>

<div class="page-content">
  <h4 class="text-center text-secondary">CONTROL DE FLOTA VyPICE S.A.C</h4>

  
</div>

<?php require('./layout/footer.php'); ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // ===== Altas/Bajas (línea)
  const abData = <?= json_encode($altas_bajas, JSON_UNESCAPED_UNICODE) ?>;
  const abLabels = abData.map(r => r.semana_iso);
  const abAltas = abData.map(r => Number(r.altas));
  const abBajas = abData.map(r => Number(r.bajas));

  new Chart(document.getElementById('chartAltasBajas').getContext('2d'), {
    type: 'line',
    data: {
      labels: abLabels,
      datasets: [{
          label: 'Personal Activo',
          data: abAltas,
          tension: .3,
          borderWidth: 2,
          fill: false
        },
        {
          label: 'Personal Cesado',
          data: abBajas,
          tension: .3,
          borderWidth: 2,
          fill: false
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      }
    }
  });

  // ===== Cursos (doughnut)
  const cursosData = <?= json_encode($cursos_resumen, JSON_UNESCAPED_UNICODE) ?>;
  const cursosLabels = cursosData.map(r => r.resultado);
  const cursosValues = cursosData.map(r => Number(r.total));

  new Chart(document.getElementById('chartCursos').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: cursosLabels,
      datasets: [{
        data: cursosValues
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });
</script>