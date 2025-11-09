<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
  header('location:login/login.php');
  exit;
}

// Validar acceso de conductor: solo pueden ver kilometrajes
include "../modelo/validar_conductor.php";

include "../modelo/conexion.php";
date_default_timezone_set('America/Lima');

/* ========== KPIs ========== */

// Total de vehículos
$kpi_vehiculos = (int)($conexion->query("SELECT COUNT(*) AS c FROM vehiculos")->fetch_assoc()['c'] ?? 0);

// Total de conductores (vinculados a usuario/persona)
$kpi_conductores = (int)($conexion->query("SELECT COUNT(*) AS c FROM conductor")->fetch_assoc()['c'] ?? 0);

// Unidades mineras
$kpi_unidades = (int)($conexion->query("SELECT COUNT(*) AS c FROM unidad_minera")->fetch_assoc()['c'] ?? 0);

// Mantenimientos del mes actual
$kpi_mant_mes = (int)($conexion->query("
    SELECT COUNT(*) AS c
    FROM mantenimientos
    WHERE YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())
")->fetch_assoc()['c'] ?? 0);

// Gasto de mantenimiento del mes actual
$row_gasto = $conexion->query("
    SELECT ROUND(COALESCE(SUM(gasto_mantenimiento),0),2) AS total
    FROM mantenimientos
    WHERE YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())
")->fetch_assoc();
$kpi_gasto_mes = $row_gasto ? $row_gasto['total'] : 0;

// Certificados próximos a vencer en 30 días
$kpi_cert_prox = (int)($conexion->query("
    SELECT COUNT(*) AS c
    FROM certificados
    WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['c'] ?? 0);

/* ========== CHART: Mantenimientos últimos 6 meses (Preventivo/Correctivo) ========== */
$mant_labels = [];
$mant_prev = [];
$mant_corr = [];
$res = $conexion->query("
    SELECT DATE_FORMAT(fecha, '%Y-%m') AS ym,
           SUM(CASE WHEN tipo='Preventivo' THEN 1 ELSE 0 END) AS prev_count,
           SUM(CASE WHEN tipo='Correctivo' THEN 1 ELSE 0 END) AS corr_count
    FROM mantenimientos
    WHERE fecha >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH)
    GROUP BY ym
    ORDER BY ym
");
while ($r = $res->fetch_assoc()) {
  $mant_labels[] = $r['ym'];
  $mant_prev[] = (int)$r['prev_count'];
  $mant_corr[] = (int)$r['corr_count'];
}

/* ========== CHART: Kilometraje semanal (últimas 8 semanas ISO) ========== */
$kms_labels = [];
$kms_values = [];
$res = $conexion->query("
    SELECT YEARWEEK(fecha_registro, 3) AS yws,
           ROUND(COALESCE(SUM(kilometraje),0),2) AS km_total
    FROM kilometraje_semanal
    WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
    GROUP BY yws
    ORDER BY yws
");
while ($r = $res->fetch_assoc()) {
  $kms_labels[] = $r['yws'];
  $kms_values[] = (float)$r['km_total'];
}

/* ========== CHART: Certificados por estado ========== */
$cert_vig = (int)($conexion->query("
    SELECT COUNT(*) AS c
    FROM certificados
    WHERE fecha_vencimiento >= CURDATE()
")->fetch_assoc()['c'] ?? 0);
$cert_prox = $kpi_cert_prox;
$cert_venc = (int)($conexion->query("
    SELECT COUNT(*) AS c
    FROM certificados
    WHERE fecha_vencimiento < CURDATE()
")->fetch_assoc()['c'] ?? 0);

/* ========== TABLA: Certificados próximos (30 días) ========== */
$cert_prox_rows = $conexion->query("
    SELECT v.matricula, c.tipo_certificado, c.fecha_emision, c.fecha_vencimiento,
           DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias_restantes
    FROM certificados c
    JOIN vehiculos v ON v.id_vehiculo = c.id_vehiculo
    WHERE c.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY c.fecha_vencimiento ASC
");

/* ========== TABLA: Últimos 10 mantenimientos ========== */
$ult_mant = $conexion->query("
    SELECT m.fecha, v.matricula, v.marca, v.modelo, m.tipo,
           m.kilometraje_actual, m.kilometraje_proximo, m.gasto_mantenimiento
    FROM mantenimientos m
    JOIN vehiculos v ON v.id_vehiculo = m.id_vehiculo
    ORDER BY m.fecha DESC
    LIMIT 10
");
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
  ul li:nth-child(1) .activo {
    background: #0b96d6 !important;
  }

  /* Ajusta el índice si tu sidebar cambia */

  h4.text-secondary {
    color: #374151 !important;
    font-weight: 800;
    letter-spacing: .3px;
  }

  .card-kpi {
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
  }

  .card-kpi .kpi-label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
  }

  .card-kpi .kpi-value {
    font-size: 26px;
    font-weight: 800;
    color: #111827;
    margin-top: 6px;
  }

  .card-kpi .kpi-sub {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
  }

  .block {
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
    margin-top: 14px;
  }

  .table thead th,
  .thead-dark th {
    background: #0b96d6 !important;
    color: #fff !important;
    border-color: #0b86c0 !important;
  }

  /* Colores EXACTOS de los botones DataTables */
  .dt-button {
    border: none !important;
    border-radius: 6px !important;
    padding: 6px 12px !important;
    font-size: 13px !important;
    margin-right: 6px !important;
    color: #fff !important;
  }

  .dt-button.buttons-excel {
    background-color: #28a745 !important;
  }

  .dt-button.buttons-pdf {
    background-color: #dc3545 !important;
  }

  @media (max-width: 767.98px) {
    .kpi-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }
  }

  @media (min-width: 768px) {
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }
  }

  @media (min-width: 1200px) {
    .kpi-grid {
      grid-template-columns: repeat(6, 1fr);
    }
  }
</style>

<div class="page-content">
  <h4 class="text-center text-secondary">CONTROL DE FLOTA VyPICE</h4>

  <!-- KPIs -->
  <div class="kpi-grid">
    <div class="card-kpi">
      <div class="d-flex align-items-center justify-content-between">
        <span class="kpi-label">Vehículos</span>
        <i class="fa-solid fa-truck fa-lg" style="color:#0b96d6"></i>
      </div>
      <div class="kpi-value"><?= number_format($kpi_vehiculos) ?></div>
      <div class="kpi-sub">&nbsp;</div>
    </div>

    <div class="card-kpi">
      <div class="d-flex align-items-center justify-content-between">
        <span class="kpi-label">Conductores</span>
        <i class="fa-solid fa-id-card fa-lg" style="color:#0b96d6"></i>
      </div>
      <div class="kpi-value"><?= number_format($kpi_conductores) ?></div>
      <div class="kpi-sub">&nbsp;</div>
    </div>

    <div class="card-kpi">
      <div class="d-flex align-items-center justify-content-between">
        <span class="kpi-label">Unidades Mineras</span>
        <i class="fa-solid fa-industry fa-lg" style="color:#0b96d6"></i>
      </div>
      <div class="kpi-value"><?= number_format($kpi_unidades) ?></div>
      <div class="kpi-sub">&nbsp;</div>
    </div>

    <div class="card-kpi">
      <div class="d-flex align-items-center justify-content-between">
        <span class="kpi-label">Mantenimientos (mes)</span>
        <i class="fa-solid fa-wrench fa-lg" style="color:#0b96d6"></i>
      </div>
      <div class="kpi-value"><?= number_format($kpi_mant_mes) ?></div>
      <div class="kpi-sub"><?= date('F Y') ?></div>
    </div>

    <div class="card-kpi">
      <div class="d-flex align-items-center justify-content-between">
        <span class="kpi-label">Gasto mant. (mes)</span>
        <i class="fa-solid fa-sack-dollar fa-lg" style="color:#0b96d6"></i>
      </div>
      <div class="kpi-value">S/ <?= number_format((float)$kpi_gasto_mes, 2) ?></div>
      <div class="kpi-sub"><?= date('F Y') ?></div>
    </div>

    <div class="card-kpi">
      <div class="d-flex align-items-center justify-content-between">
        <span class="kpi-label">Cert. próximos (30d)</span>
        <i class="fa-solid fa-hourglass-half fa-lg" style="color:#0b96d6"></i>
      </div>
      <div class="kpi-value"><?= number_format($kpi_cert_prox) ?></div>
      <div class="kpi-sub">Desde hoy</div>
    </div>
  </div>

  <!-- Gráficos -->
  <div class="row">
    <div class="col-lg-6">
      <div class="block">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="m-0 text-secondary">Mantenimientos últimos 6 meses</h6>
        </div>
        <canvas id="chartMant6m" height="200"></canvas>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="block">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="m-0 text-secondary">Kilometraje semanal (8 semanas)</h6>
        </div>
        <canvas id="chartKms8w" height="200"></canvas>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xl-4">
      <div class="block">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="m-0 text-secondary">Certificados por estado</h6>
        </div>
        <canvas id="chartCert" height="250"></canvas>
      </div>
    </div>

    <div class="col-xl-8">
      <div class="block">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="m-0 text-secondary">Próximos certificados a vencer (30 días)</h6>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="tabla_cert_prox">
            <thead class="thead-dark">
              <tr>
                <th>Vehículo</th>
                <th>Tipo</th>
                <th>Emisión</th>
                <th>Vencimiento</th>
                <th>Días restantes</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($r = $cert_prox_rows->fetch_assoc()):
                $fe = $r['fecha_emision'];
                $de = ($fe && $fe !== '0000-00-00') ? date('d/m/Y', strtotime($fe)) : '';
                $fv = $r['fecha_vencimiento'];
                $dv = ($fv && $fv !== '0000-00-00') ? date('d/m/Y', strtotime($fv)) : '';
              ?>
                <tr>
                  <td><?= htmlspecialchars($r['matricula']) ?></td>
                  <td><?= htmlspecialchars($r['tipo_certificado']) ?></td>
                  <td data-order="<?= htmlspecialchars($fe) ?>"><?= htmlspecialchars($de) ?></td>
                  <td data-order="<?= htmlspecialchars($fv) ?>"><?= htmlspecialchars($dv) ?></td>
                  <td><?= (int)$r['dias_restantes'] ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Últimos mantenimientos -->
  <div class="block">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="m-0 text-secondary">Últimos 10 mantenimientos</h6>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered table-hover" id="tabla_ult_mant">
        <thead class="thead-dark">
          <tr>
            <th>Fecha</th>
            <th>Vehículo</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Tipo</th>
            <th>Km Actual</th>
            <th>Km Próximo</th>
            <th>Gasto</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($r = $ult_mant->fetch_assoc()):
            $f = $r['fecha'];
            $df = ($f && $f !== '0000-00-00') ? date('d/m/Y', strtotime($f)) : '';
          ?>
            <tr>
              <td data-order="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($df) ?></td>
              <td><?= htmlspecialchars($r['matricula']) ?></td>
              <td><?= htmlspecialchars($r['marca']) ?></td>
              <td><?= htmlspecialchars($r['modelo']) ?></td>
              <td><?= htmlspecialchars($r['tipo']) ?></td>
              <td><?= htmlspecialchars($r['kilometraje_actual']) ?></td>
              <td><?= htmlspecialchars($r['kilometraje_proximo']) ?></td>
              <td><?= htmlspecialchars($r['gasto_mantenimiento']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require('./layout/footer.php'); ?>

<!-- Scripts base -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
  /* Normalizar búsquedas (acentos/ñ) */
  jQuery.extend(jQuery.fn.dataTable.ext.type.search, {
    string: function(data) {
      if (!data) return '';
      if (typeof data !== 'string') return data;
      return data.normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        .replace(/ñ/g, "n").replace(/Ñ/g, "n").trim().toLowerCase();
    }
  });

  /* DataTables */
  $(function() {
    const configBase = {
      dom: 'Bfrtip',
      buttons: [{
          extend: 'excelHtml5',
          text: '<i class="fa-solid fa-file-excel"></i> Excel'
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="fa-solid fa-file-pdf"></i> PDF'
        }
      ],
      language: {
        lengthMenu: "Mostrar _MENU_ registros por página",
        zeroRecords: "No se encontraron registros",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        search: "Buscar:",
        paginate: {
          first: "Primero",
          last: "Último",
          next: "Siguiente",
          previous: "Anterior"
        }
      }
    };

    $('#tabla_cert_prox').DataTable($.extend(true, {}, configBase, {
      order: [
        [3, 'asc']
      ]
    }));
    $('#tabla_ult_mant').DataTable($.extend(true, {}, configBase, {
      order: [
        [0, 'desc']
      ]
    }));
  });

  /* Charts: datos desde PHP */
  const mantLabels = <?= json_encode($mant_labels, JSON_UNESCAPED_UNICODE) ?>;
  const mantPrev = <?= json_encode($mant_prev, JSON_UNESCAPED_UNICODE) ?>;
  const mantCorr = <?= json_encode($mant_corr, JSON_UNESCAPED_UNICODE) ?>;

  const kmsLabels = <?= json_encode($kms_labels, JSON_UNESCAPED_UNICODE) ?>;
  const kmsValues = <?= json_encode($kms_values, JSON_UNESCAPED_UNICODE) ?>;

  const certVig = <?= (int)$cert_vig ?>;
  const certProx = <?= (int)$cert_prox ?>;
  const certVenc = <?= (int)$cert_venc ?>;

  /* Chart.js (sin colores forzados; usa por defecto para mantener consistencia visual) */
  (function() {
    const ctx1 = document.getElementById('chartMant6m').getContext('2d');
    new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: mantLabels,
        datasets: [{
            label: 'Preventivo',
            data: mantPrev,
            stack: 'mant'
          },
          {
            label: 'Correctivo',
            data: mantCorr,
            stack: 'mant'
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
          x: {
            stacked: true
          },
          y: {
            stacked: true,
            beginAtZero: true,
            precision: 0
          }
        }
      }
    });

    const ctx2 = document.getElementById('chartKms8w').getContext('2d');
    new Chart(ctx2, {
      type: 'line',
      data: {
        labels: kmsLabels,
        datasets: [{
          label: 'Km totales',
          data: kmsValues,
          tension: .25
        }]
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
            beginAtZero: true
          }
        }
      }
    });

    const ctx3 = document.getElementById('chartCert').getContext('2d');
    new Chart(ctx3, {
      type: 'doughnut',
      data: {
        labels: ['Vigentes', 'Próximos 30d', 'Vencidos'],
        datasets: [{
          data: [certVig, certProx, certVenc]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        cutout: '60%'
      }
    });
  })();
</script>