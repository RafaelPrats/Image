<?php

namespace yura\Providers;

use Illuminate\Support\ServiceProvider;
use yura\Modelos\{
    Actividad,
    ActividadManoObra,
    ActividadProducto,
    Aerolinea,
    AgenciaCarga,
    Area,
    Camion,
    Ciclo,
    ClasificacionVerde,
    CostoHoras,
    CostosSemana,
    DetalleClasificacionVerde,
    GrupoMenu,
    Indicador,
    IndicadorSemana,
    IndicadorVariedad,
    IndicadorVariedadSemana,
    IntervaloIndicador,
    InventarioFrio,
    Lote,
    LoteRE,
    ManoObra,
    Menu,
    Modulo,
    Monitoreo,
    MonitoreoCalibre,
    Notificacion,
    NotificacionUsuario,
    ClasificacionBlanco,
    Cliente,
    ClienteAgenciaCarga,
    ClienteConsignatario,
    ClienteDatoExportacion,
    ClientePedidoEspecificacion,
    CodigoVentureAgenciaCarga,
    Comprobante,
    Conductor,
    Consignatario,
    Contacto,
    ContactoClienteAgenciaCarga,
    Cosecha,
    DataTallos,
    DatosExportacion,
    DesgloseEnvioFactura,
    Despacho,
    DetalleCliente,
    DetalleClienteContacto,
    DetalleDespacho,
    DetalleEnvio,
    DetalleEspecificacionEmpaque,
    DetalleEspecificacionEmpaqueRamosCaja,
    DetalleGuiaRemision,
    DetallePedido,
    DetallePedidoDatoExportacion,
    DistribucionMixtos,
    DistribucionMixtosSemana,
    DistribucionVariedad,
    Documento,
    Empaque,
    Envio,
    Especificacion,
    EspecificacionEmpaque,
    Marca,
    OtrosGastos,
    Pedido,
    Planta,
    Precio,
    Producto,
    ProductoYuraVenture,
    ProyCortes,
    ProyeccionModulo,
    ProyeccionModuloSemana,
    ProyeccionVentaSemanalReal,
    ProyVariedadCortes,
    ProyVariedadSemana,
    RecepcionClasificacionVerde,
    Regalias,
    ResumenAreaSemanal,
    ResumenCostosSemanal,
    ResumenSemanaCosecha,
    Rol,
    Rol_Submenu,
    Sector,
    Semana,
    StockApertura,
    StockEmpaquetado,
    StockGuarde,
    Submenu,
    Temperatura,
    UnidadMedida,
    Usuario,
    UsuarioSector,
    Variedad,
    VariedadClasificacionUnitaria,
    ResumenSaldoProyeccionVentaSemanal,
    Transportista
};
use yura\Observers\ClasificacionBlanco\ClasificacionBlancoObserver;
use yura\Observers\ClasificacionBlanco\InventarioFrioObserver;
use yura\Observers\ClasificacionVerde\{
    ClasificacionVerdeObserver,
    DetalleClasificacionVerdeObserver,
    LoteReObserver,
    MonitoreoCalibreObserver,
    RecepcionClasificacionVerdeObserver,
    StockAperturaObserver,
    StockEmpaquetadoObserver,
    StockGuardeObserver
};
use yura\Observers\Cliclo\CicloObserver;
use yura\Observers\Cliente\ClienteAgenciaCargaObserver;
use yura\Observers\Cliente\ClienteConsignatarioObserver;
use yura\Observers\Cliente\ClienteDatosExportacionObserver;
use yura\Observers\Cliente\ClienteObserver;
use yura\Observers\Cliente\ClientePedidoEspecificacionObserver;
use yura\Observers\Cliente\ContactoClienteAgenciaCargaObserver;
use yura\Observers\Cliente\ContactoObserver;
use yura\Observers\Cliente\DetalleClienteContactoObserver;
use yura\Observers\Cliente\DetalleClienteObserver;
use yura\Observers\Cliente\DocumentoObserver;
use yura\Observers\Comercializacion\AerolineaObserver;
use yura\Observers\Comercializacion\AgenciaCargaObserver;
use yura\Observers\Comercializacion\CamionObserver;
use yura\Observers\Comercializacion\CodigoVentureAgenciaCargaObserver;
use yura\Observers\Comercializacion\ComprobanteObserver;
use yura\Observers\Comercializacion\ConductorObserver;
use yura\Observers\Comercializacion\ConsignatarioObserver;
use yura\Observers\Comercializacion\DataTallosObserver;
use yura\Observers\Comercializacion\DatosExportacionObserver;
use yura\Observers\Comercializacion\DesgloseEnvioFacturaObserver;
use yura\Observers\Comercializacion\DespachoObserver;
use yura\Observers\Comercializacion\DetalleDespachoObserver;
use yura\Observers\Comercializacion\DetalleEnvioObserver;
use yura\Observers\Comercializacion\DetalleEspecificacionEmpaqueRamosCajaObserver;
use yura\Observers\Comercializacion\DetalleGuiaRemisionObserver;
use yura\Observers\Comercializacion\DetallePedidoDatoExportacionObserver;
use yura\Observers\Comercializacion\DetallePedidoObserver;
use yura\Observers\Comercializacion\EmpaqueObserver;
use yura\Observers\ProyNintanga\DistribucionMixtosObserver;
use yura\Observers\Comercializacion\EnvioObserver;
use yura\Observers\Comercializacion\MarcaObserver;
use yura\Observers\Comercializacion\PedidoObserver;
use yura\Observers\Comercializacion\ProductoYuraVentureObserver;
use yura\Observers\Comercializacion\TransportistaObserver;
use yura\Observers\Lote\LoteObserver;
use yura\Observers\Modulo\ModuloObserver;
use yura\Observers\Monitoreo\MonitoreoObserver;
use yura\Observers\ProyeccionModulo\ProyeccionModuloObserver;
use yura\Observers\Sector\SectorObserver;
use yura\Observers\Semana\SemanaObserver;
use yura\Observers\Temperatura\TemperaturaObserver;
use yura\Observers\Cosecha\ResumenCosechaObserver;
use yura\Observers\Cosecha\CosechaObserver;
use yura\Observers\Costos\ActividadManoObraObserver;
use yura\Observers\Costos\ActividadProductoObserver;
use yura\Observers\Costos\CostoSemanaObserver;
use yura\Observers\Costos\ManoObraObserver;
use yura\Observers\Especificacion\DetalleEspecificacionEmpaqueObserver;
use yura\Observers\Especificacion\EspecificacionEmpaqueObserver;
use yura\Observers\Especificacion\EspecificacionObserver;
use yura\Observers\Gastos\OtrosGastosObserver;
use yura\Observers\Indicador\IndicadorObserver;
use yura\Observers\Indicador\IndicadorSemanaObserver;
use yura\Observers\Indicador\IndicadorVariedadObserver;
use yura\Observers\Indicador\IndicadorVariedadSemanaObserver;
use yura\Observers\Indicador\IntervaloIndicadorObserver;
use yura\Observers\Insumos\ActividadObserver;
use yura\Observers\Insumos\AreaObserver;
use yura\Observers\Insumos\ProductoObserver;
use yura\Observers\Menu\GrupoMenuObserver;
use yura\Observers\Menu\MenuObserver;
use yura\Observers\Menu\SubMenuObserver;
use yura\Observers\Notificacion\NotificacionObserver;
use yura\Observers\Notificacion\NotificacionUsuarioObserver;
use yura\Observers\Planta\PlantaObserver;
use yura\Observers\Precio\PrecioObserver;
use yura\Observers\Proyeccion\ProyeccionVentaSemanalRealObserver;
use yura\Observers\Proyeccion\ResumenSaldoProyVentaSemanalObserver;
use yura\Observers\ProyeccionModulo\ProyeccionModuloSemanaObserver;
use yura\Observers\ProyNintanga\DistribucionMixtosSemanaObserver;
use yura\Observers\ProyNintanga\DistribucionVariedadObserver;
use yura\Observers\ProyNintanga\ProyCortesObserver;
use yura\Observers\ProyNintanga\ProyVariedadCortesObserver;
use yura\Observers\ProyNintanga\ProyVariedadSemanaObserver;
use yura\Observers\Regalias\RegaliasObserver;
use yura\Observers\Resumen\ResumenAreaSemanalObserver;
use yura\Observers\Resumen\ResumenCostoSemanalObserver;
use yura\Observers\Rol\RolObserver;
use yura\Observers\Rol\RolSubMenuObserver;
use yura\Observers\UnidadMedida\UnidadMedidaObserver;
use yura\Observers\Usuarios\UsuarioObserver;
use yura\Observers\Usuarios\UsuarioSectorObserver;
use yura\Observers\Variedad\VariedadObserver;
use yura\Observers\VariedadClasificacionUnitaria\VariedadClasificacionUnitariaObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if($this->app->environment('production')){
            $this->app['request']->server->set('HTTPS','on');
        }

        /*Ciclo::observe(CicloObserver::class);
        ProyeccionModulo::observe(ProyeccionModuloObserver::class);
        Semana::observe(SemanaObserver::class);
        Sector::observe(SectorObserver::class);
        Modulo::observe(ModuloObserver::class);
        Lote::observe(LoteObserver::class);
        Monitoreo::observe(MonitoreoObserver::class);
        Temperatura::observe(TemperaturaObserver::class);
        ClasificacionVerde::observe(ClasificacionVerdeObserver::class);
        DetalleClasificacionVerde::observe(DetalleClasificacionVerdeObserver::class);
        RecepcionClasificacionVerde::observe(RecepcionClasificacionVerdeObserver::class);
        ResumenSemanaCosecha::observe(ResumenCosechaObserver::class);
        MonitoreoCalibre::observe(MonitoreoCalibreObserver::class);
        LoteRE::observe(LoteReObserver::class);
        StockApertura::observe(StockAperturaObserver::class);
        StockGuarde::observe(StockGuardeObserver::class);
        StockEmpaquetado::observe(StockEmpaquetadoObserver::class);
        Usuario::observe(UsuarioObserver::class);
        UsuarioSector::observe(UsuarioSectorObserver::class);
        Rol::observe(RolObserver::class);
        Rol_Submenu::observe(RolSubMenuObserver::class);
        GrupoMenu::observe(GrupoMenuObserver::class);
        Menu::observe(MenuObserver::class);
        Submenu::observe(SubMenuObserver::class);
        Notificacion::observe(NotificacionObserver::class);
        NotificacionUsuario::observe(NotificacionUsuarioObserver::class);
        Planta::observe(PlantaObserver::class);
        Variedad::observe(VariedadObserver::class);
        Precio::observe(PrecioObserver::class);
        VariedadClasificacionUnitaria::observe(VariedadClasificacionUnitariaObserver::class);
        Regalias::observe(RegaliasObserver::class);
        UnidadMedida::observe(UnidadMedidaObserver::class);
        ProyeccionModuloSemana::observe(ProyeccionModuloSemanaObserver::class);
       // Indicador::observe(IndicadorObserver::class);
        IntervaloIndicador::observe(IntervaloIndicadorObserver::class);
        ProyeccionVentaSemanalReal::observe(ProyeccionVentaSemanalRealObserver::class);
        ResumenSaldoProyeccionVentaSemanal::observe(ResumenSaldoProyVentaSemanalObserver::class);
        //IndicadorVariedad::observe(IndicadorVariedadObserver::class);
        //ResumenAreaSemanal::observe(ResumenAreaSemanalObserver::class);
        OtrosGastos::observe(OtrosGastosObserver::class);
        ResumenCostosSemanal::observe(ResumenCostoSemanalObserver::class);
        IndicadorSemana::observe(IndicadorSemanaObserver::class);
       // IndicadorVariedadSemana::observe(IndicadorVariedadSemanaObserver::class);
        CostoHoras::observe(CostoHorasObserver::class);
        CostosSemana::observe(CostoSemanaObserver::class);
        ActividadProducto::observe(ActividadProductoObserver::class);
        ActividadManoObra::observe(ActividadManoObraObserver::class);
        Area::observe(AreaObserver::class);
        Actividad::observe(ActividadObserver::class);
        Producto::observe(ProductoObserver::class);
        ManoObra::observe(ManoObraObserver::class);
        InventarioFrio::observe(InventarioFrioObserver::class);
        ClasificacionBlanco::observe(ClasificacionBlancoObserver::class);
        //Cosecha::observe(CosechaObserver::class);
        Cliente::observe(ClienteObserver::class);
        DetalleCliente::observe(DetalleClienteObserver::class);
        Documento::observe(DocumentoObserver::class);
        Contacto::observe(ContactoObserver::class);
        DetalleClienteContacto::observe(DetalleClienteContactoObserver::class);
        ClienteConsignatario::observe(ClienteConsignatarioObserver::class);
        ClienteAgenciaCarga::observe(ClienteAgenciaCargaObserver::class);
        ContactoClienteAgenciaCarga::observe(ContactoClienteAgenciaCargaObserver::class);
        Especificacion::observe(EspecificacionObserver::class);
        EspecificacionEmpaque::observe(EspecificacionEmpaqueObserver::class);
        DetalleEspecificacionEmpaque::observe(DetalleEspecificacionEmpaqueObserver::class);
        ClientePedidoEspecificacion::observe(ClientePedidoEspecificacionObserver::class);
        DatosExportacion::observe(DatosExportacionObserver::class);
        ClienteDatoExportacion::observe(ClienteDatosExportacionObserver::class);
        AgenciaCarga::observe(AgenciaCargaObserver::class);
        CodigoVentureAgenciaCarga::observe(CodigoVentureAgenciaCargaObserver::class);
        Consignatario::observe(ConsignatarioObserver::class);
        Aerolinea::observe(AerolineaObserver::class);
        Marca::observe(MarcaObserver::class);
        Transportista::observe(TransportistaObserver::class);
        Camion::observe(CamionObserver::class);
        Conductor::observe(ConductorObserver::class);
        Pedido::observe(PedidoObserver::class);
        DetallePedido::observe(DetallePedidoObserver::class);
        DetalleEspecificacionEmpaqueRamosCaja::observe(DetalleEspecificacionEmpaqueRamosCajaObserver::class);
        DataTallos::observe(DataTallosObserver::class);
        DetallePedidoDatoExportacion::observe(DetallePedidoDatoExportacionObserver::class);
        Comprobante::observe(ComprobanteObserver::class);
        Envio::observe(EnvioObserver::class);
        DetalleEnvio::observe(DetalleEnvioObserver::class);
        ProductoYuraVenture::observe(ProductoYuraVentureObserver::class);
        DesgloseEnvioFactura::observe(DesgloseEnvioFacturaObserver::class);
        DetalleGuiaRemision::observe(DetalleGuiaRemisionObserver::class);
        Despacho::observe(DespachoObserver::class);
        DetalleDespacho::observe(DetalleDespachoObserver::class);
        DistribucionMixtos::observe(DistribucionMixtosObserver::class);
        ProyVariedadCortes::observe(ProyVariedadCortesObserver::class);
        ProyCortes::observe(ProyCortesObserver::class);
        ProyVariedadSemana::observe(ProyVariedadSemanaObserver::class);
        DistribucionVariedad::observe(DistribucionVariedadObserver::class);
        DistribucionMixtosSemana::observe(DistribucionMixtosSemanaObserver::class);
        Empaque::observe(EmpaqueObserver::class);*/
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
