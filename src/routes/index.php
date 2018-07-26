<?php
    require_once('utils.php');
    require_once("login.php");
    require_once("route.reportes.php");

    require_once("registros/pacientes/index.php");
    require_once("registros/sucursales/index.php");
    require_once("registros/entidades/index.php");
    require_once("registros/personales/index.php");
    require_once("registros/doctors/index.php");
    require_once("registros/proveedores/index.php");
    require_once("registros/examens/index.php");
    require_once("registros/permisos.php");
    require_once("registros/materiales.php");
    require_once("registros/compras.php");
    require_once("registros/inventario.php");
    require_once("registros/usuarios/index.php");

    require_once("planilla.php");
    require_once("registros/configuration.users.php");
    require_once("registros/reportes.php");

    require_once("registros/examenes/index.php");

    // nuevas routas
    require_once("registros/promociones/route.promocion.php");
    require_once("registros/entidad.examen/route.entidad.examen.php");
    require_once("registros/examenes.materiales/route.examenes.materiales.php");
    require_once("registros/solicitud/route.solicitud.php");
    require_once("registros/catalogoCampos/route.catalogo.campos.php");

    require_once('registros/route.user.php');
    require_once('registros/paypal.php');

    require_once('registros/perfil.php');
    require_once('registros/send_email.php');
    require_once('registros/route.renta.php');
    require_once('registros/citas.php');
    require_once('registros/bitacora.php');
    require_once('registros/web.php');
    require_once('registros/noticias.php');
    require_once('registros/backup/backup.php');

    require_once('registros/finanza/route.finanza.php');
    
    // Route for app
    require_once('registros/route.app.php');
    require_once('reporte.examenes.php');
?>
