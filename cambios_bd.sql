alter table producto add codigo_jire varchar(500) null;

create table bodega
(
	id_bodega int auto_increment,
	id_configuracion_empresa int not null,
	nombre varchar(500) not null,
	estado boolean default true not null,
	fecha_registro timestamp default current_timestamp not null,
	constraint bodega_pk
		primary key (id_bodega),
	constraint bodega_configuracion_empresa_id_configuracion_empresa_fk
		foreign key (id_configuracion_empresa) references configuracion_empresa (id_configuracion_empresa)
			on update cascade
);

create index bodega_id_configuracion_empresa_index
	on bodega (id_configuracion_empresa);

create unique index bodega_id_bodega_uindex on bodega (id_bodega);


create table producto_bodega
(
	id_producto_bodega int auto_increment,
	id_producto int not null,
	id_bodega int not null,
	cantidad float not null,
	fecha_registro timestamp default current_timestamp not null,
	constraint producto_bodega_pk
		primary key (id_producto_bodega),
	constraint producto_bodega_bodega_id_bodega_fk
		foreign key (id_bodega) references bodega (id_bodega)
			on update cascade,
	constraint producto_bodega_producto_id_producto_fk
		foreign key (id_producto) references producto (id_producto)
			on update cascade
);

create index producto_bodega_id_bodega_index
	on producto_bodega (id_bodega);

create index producto_bodega_id_producto_index
	on producto_bodega (id_producto);

create unique index producto_bodega_id_producto_bodega_uindex
	on producto_bodega (id_producto_bodega);


alter table producto
	add aplicacion varchar(100) null comment 'CAJA, BOUNCHE';


alter table producto modify fecha_registro datetime default CURRENT_TIMESTAMP not null after aplicacion;


create table detalle_especificacionempaque_producto
(
	id_detalle_especificacionempaque_producto int auto_increment,
	id_producto int not null,
	id_detalle_especificacionempaque int not null,
	cantidad float default 0 not null,
	fecha_registro timestamp default current_timestamp not null,
	constraint detalle_especificacionempaque_producto_pk
		primary key (id_detalle_especificacionempaque_producto),
	constraint det_esp_emp_producto_det_esp_epm_id_det_esp_emp_fk
		foreign key (id_detalle_especificacionempaque) references detalle_especificacionempaque (id_detalle_especificacionempaque)
			on update cascade,
	constraint detalle_especificacionempaque_producto_producto_id_producto_fk
		foreign key (id_producto) references producto (id_producto)
			on update cascade
);

create index detalle_especificacionempaque_producto_id_producto_index
	on detalle_especificacionempaque_producto (id_producto);

create unique index det_esp_emp_producto_id_det_esp_emp_producto_uindex
	on detalle_especificacionempaque_producto (id_detalle_especificacionempaque_producto);


