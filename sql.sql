/* update detalle_especificacionempaque
set id_empaque_p = 167 where id_detalle_especificacionempaque
in (
select distinct det_esp_emp.id_detalle_especificacionempaque  from pedido as p
inner join detalle_pedido as dp on p.id_pedido = dp.id_pedido
inner join cliente_pedido_especificacion as cpe on cpe.id_cliente_pedido_especificacion = dp.id_cliente_especificacion
inner join especificacion as esp on esp.id_especificacion = cpe.id_especificacion
inner join especificacion_empaque as esp_emp on esp.id_especificacion = esp_emp.id_especificacion
inner join detalle_especificacionempaque as det_esp_emp on esp_emp.id_especificacion_empaque = det_esp_emp.id_especificacion_empaque
and det_esp_emp.id_empaque_p = 166
inner join variedad as v on det_esp_emp.id_variedad = v.id_variedad and v.id_planta=2
inner join planta as pl on pl.id_planta = v.id_planta and pl.id_planta=2
where p.id_cliente = 59  and fecha_pedido >= '2022-11-28'
) */


