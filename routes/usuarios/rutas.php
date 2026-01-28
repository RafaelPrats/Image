<?php

Route::get('usuarios', 'UsuarioController@inicio');
Route::get('usuarios/buscar', 'UsuarioController@buscar_usuarios');
Route::post('usuarios/eliminar', 'UsuarioController@eliminar_usuarios');
Route::get('usuarios/add', 'UsuarioController@add_usuarios');
Route::post('usuarios/store', 'UsuarioController@store_usuarios');
Route::get('usuarios/ver_usuario', 'UsuarioController@ver_usuario');
Route::post('usuarios/update_usuario', 'UsuarioController@update_usuario');
Route::post('usuarios/update_image_perfil', 'UsuarioController@update_image_perfil');
Route::post('usuarios/update_password', 'UsuarioController@update_password');
Route::get('usuarios/exportar', 'UsuarioController@exportar_usuarios');
Route::get('usuarios/asignar_sectores', 'UsuarioController@asignar_sectores');
Route::post('usuarios/store_usuario_sector', 'UsuarioController@store_usuario_sector');
