<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('login', 'YuraController@login');
Route::post('login', 'YuraController@verificaUsuario');
Route::get('logout', 'YuraController@logout');

Route::get('configuracion/inputs_dinamicos_detalle_empaque', 'ConfiguracionEmpresaController@vistaInputsDetallesEmpaque')->name('view.inputs_detalle_empaque');
Route::get('configuracion/campos_empaques', 'ConfiguracionEmpresaController@campos_empaque')->name('view.campos_empaque');

Route::group(['middleware' => 'autenticacion'], function () {
    Route::group(['middleware' => 'controlsession'], function () {
        Route::get('/', 'YuraController@inicio');
        Route::get('select_filtro_variedad', 'YuraController@select_filtro_variedad');
        Route::get('detallar_indicador', 'YuraController@detallar_indicador');
        Route::get('mostrar_indicadores_claves', 'YuraController@mostrar_indicadores_claves');
        Route::get('cargar_accesos_directos', 'YuraController@cargar_accesos_directos');
        Route::get('cargar_submenu_crm', 'YuraController@cargar_submenu_crm');
        Route::post('save_config_user', 'YuraController@save_config_user');
        Route::get('perfil', 'YuraController@perfil');
        Route::get('perfil/admin_accesos', 'YuraController@admin_accesos');
        Route::post('perfil/seleccionar_submenu', 'YuraController@seleccionar_submenu');
        Route::post('perfil/update_usuario', 'YuraController@update_usuario');
        Route::post('perfil/update_image_perfil', 'YuraController@update_image_perfil');
        Route::post('perfil/update_password', 'YuraController@update_password');

        Route::post('usuarios/get_usuario_json', 'UsuarioController@get_usuario_json');

        Route::get('select_planta', 'YuraController@select_planta');
        Route::get('ver_segundo_plano', 'YuraController@ver_segundo_plano');
        Route::get('listar_segundo_plano', 'YuraController@listar_segundo_plano');
        Route::post('proceso_segundo_plano/completar_pedido_proceso', 'YuraController@completar_pedido_proceso');
        Route::post('proceso_segundo_plano/delete_pedido_proceso', 'YuraController@delete_pedido_proceso');

        include 'documento/rutas.php';
        include 'crm/dashboard.php';
        Route::get('pedidos/crear_packing_list/{id_pedido}/{despacho?}', 'PedidoController@crear_packing_list');

        Route::get('crm_postcosecha/actualizar_cosecha_x_variedad', 'CRM\crmPostocechaController@actualizar_cosecha_x_variedad');
        Route::get('recepcion/ver_rendimiento', 'RecepcionController@ver_rendimiento');

        Route::group(['middleware' => 'permiso'], function () {
            /* ========================== COMERCIALIZACION ========================*/
            include 'comercializacion/especificaciones.php';
            include 'comercializacion/proyectos.php';
            include 'comercializacion/ingreso_daes.php';
            include 'comercializacion/distribucion_mixtos.php';
            include 'comercializacion/postcosecha.php';
            include 'comercializacion/etiquetas.php';
            include 'comercializacion/inventario_cuarto_frio.php';
            include 'comercializacion/hoja_ruta.php';

            /* ========================== POSTCPCECHA ========================*/
            include 'postcocecha/lotes.php';
            include 'postcocecha/clasificacion_blanco.php';
            include 'postcocecha/reporte_por_marcaciones.php';
            include 'postcocecha/cuarto_frio.php';
            include 'postcocecha/despachos.php';
            include 'postcocecha/apertura.php';
            include 'postcocecha/clasificacion_verde.php';
            include 'postcocecha/recepcion.php';
            include 'postcocecha/clientes.php';
            include 'postcocecha/consignatario.php';
            include 'postcocecha/modificaciones_pedidos.php';
            include 'postcocecha/cuadre_flor.php';
            include 'postcocecha/historial_ordenes_fija.php';
            include 'postcocecha/desechos_frio.php';
            include 'postcocecha/ingresos_frio.php';
            include 'postcocecha/clasificadores.php';
            include 'postcocecha/distribucion_posco.php';

            include 'sectores_modulos/rutas.php';
            include 'semanas/rutas.php';
            include 'plantas_variedades/rutas.php';

            include 'menu_sistema/rutas.php';
            include 'permisos/rutas.php';
            include 'usuarios/rutas.php';

            include 'configuracion_empresa/rutas.php';
            include 'postcocecha/agencias_carga.php';
            include 'postcocecha/marcas.php';
            include 'postcocecha/pedidos_ventas.php';
            include 'postcocecha/envios.php';
            include 'postcocecha/aerolinea.php';
            include 'postcocecha/especificacion.php';
            include 'postcocecha/cajas_presentaciones.php';
            include 'postcocecha/precio.php';
            include 'postcocecha/dato_exportacion.php';
            include 'postcocecha/transportista.php';
            include 'postcocecha/etiqueta.php';
            include 'postcocecha/etiqueta_factura.php';
            include 'postcocecha/ingreso_guias_daes.php';
            include 'postcocecha/reporte_ventas_semanales.php';

            /* ========================== CRM ========================*/
            include 'crm/postcosecha.php';
            include 'crm/ventas.php';
            include 'crm/ventas_m2.php';
            include 'crm/crm_area.php';
            include 'crm/rendimiento_desecho.php';
            include 'crm/tbl_postcosecha.php';
            include 'crm/fue.php';
            include 'crm/regalias_semanas.php';
            include 'crm/tbl_ventas.php';
            include 'crm/tbl_rendimiento.php';
            include 'crm/fenograma_ejecucion.php';
            include 'crm/crm_proyeccion.php';
            include 'crm/propagacion.php';
            include 'crm/cosecha_diaria.php';
            include 'crm/cosecha_estimada.php';
            include 'crm/resumen_verde.php';
            include 'crm/resumen_cosecha.php';
            include 'crm/reporte_cajas.php';

            /* ========================== FACTURACIÃ“N ========================*/
            include 'facturacion/tipo_comprobante.php';
            include 'facturacion/tipo_identificacion.php';
            include 'facturacion/tipo_impuesto.php';
            include 'facturacion/emision_comprobante.php';
            include 'facturacion/codigo_dae.php';
            include 'facturacion/producto_venture.php';
            include 'facturacion/orden_factura.php';

            /* ================== IMPORTAR DATA =================== */
            include 'importar_data/rutas.php';
            include 'importar_data/importar_venta.php';

            /* ================== NOTIFICACIONES =================== */
            include 'notificaciones/rutas.php';

            /* ================== BODEGA =================== */
            include 'bodega/productos.php';

            /* ================== BUQUETS =================== */
            include 'buquets/distribucion_recetas.php';

            /* ================== PROYECCIONES =================== */
            include 'proyecciones/cosecha.php';
            include 'proyecciones/ventas_x_cliente.php';
            include 'proyecciones/resumen_total.php';
            include 'proyecciones/mano_obra.php';
            include 'proyecciones/monitoreo_ciclos.php';
            include 'proyecciones/curva_estandar.php';
            include 'proyecciones/temperaturas.php';

            /* ================== COSTOS =================== */
            include 'costos/insumo.php';
            include 'costos/mano_obra.php';
            include 'costos/importar.php';
            include 'costos/generales.php';
            include 'costos/fenograma_costos.php';
            include 'costos/costos_hora.php';

            /* ================== PROPAGACION =============== */
            include 'propagacion/camas_ciclos.php';
            include 'propagacion/configuraciones.php';
            include 'propagacion/cosecha_plantas_madres.php';
            include 'propagacion/fenograma.php';
            include 'propagacion/enraizamiento.php';
            include 'propagacion/disponibilidad.php';
            include 'propagacion/resumen_ptas_madres.php';

            /* ================== RRHH =================== */
            include 'rrhh/parametros.php';
            include 'rrhh/personal.php';

            /* ================== CAMPO =================== */
            include 'campo/aplicaciones.php';
            include 'campo/proyeccion_aplicaciones.php';
            include 'campo/ciclo_luz.php';
            include 'campo/reporte_luz.php';
            include 'campo/ingreso_labores.php';
            include 'campo/reporte_labores.php';
            include 'campo/historico_luz.php';

            /* ================== DB =================== */
            include 'db/rutas.php';
            include 'db/unidad_medida.php';

            /* ================== CLASIFICACIONES =================== */
            include 'clasificacion/por_ramo.php';
            include 'clasificacion/unitaria.php';

            /* ================== PROYECCIONES NINTANGA =================== */
            include 'proyeccion_nintanga/ingresos_diarios.php';
            include 'proyeccion_nintanga/proyeccion_semana.php';
            include 'proyeccion_nintanga/distribucion_cosecha.php';
            include 'proyeccion_nintanga/distribucion_semana.php';
            include 'proyeccion_nintanga/disponibilidad_diaria.php';
        });

        include 'colores/rutas.php';
        include 'codigo_barra/rutas.php';
        include 'facturacion/comprobante.php';

        /* MANUAL USUARIO */
        Route::get('cargar_manual_usuario', 'ManualUsuarioController@cargar_manual_usuario');
    });
});
include 'notificaciones/otras.php';

Route::get('pagina_a', 'ControladorNuevo@contenido_a');
Route::get('pagina_b', 'ControladorNuevo@contenido_b');

Route::get('test', function (\Illuminate\Http\Request $request) {
    return view('welcome');
});
