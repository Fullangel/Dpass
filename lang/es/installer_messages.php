<?php

return array (
  'title' => 'Instalador de la aplicación Visitor Pass',
  'next' => 'Siguiente Paso',
  'back' => 'Anterior',
  'finish' => 'Instalar',
  'forms' => 
  array (
    'errorTitle' => 'Se produjeron los siguientes errores:',
  ),
  'welcome' => 
  array (
    'templateTitle' => 'Bienvenido al Instalador Fácil de Visitor Pass',
    'title' => 'Sistema de gestión de pases de visitantes',
    'message' => 'Asistente de instalación y configuración sencillo para Visitor Pass.',
    'next' => 'Verificar Requisitos',
  ),
  'requirements' => 
  array (
    'templateTitle' => 'Paso 1 | Requisitos del Servidor',
    'title' => 'Requisitos del Servidor',
    'next' => 'Verificar Permisos',
  ),
  'permissions' => 
  array (
    'templateTitle' => 'Paso 2 | Permisos',
    'title' => 'Permisos',
    'next' => 'Configurar Entorno',
  ),
  'purchase-code' => 
  array (
    'templateTitle' => 'Paso 2 | Código de Compra',
    'title' => 'Código de Compra',
    'next' => 'Verificar tu código de compra',
    'form' => 
    array (
      'purchase_code_label' => 'Código de Compra',
      'purchase_username_label' => 'Nombre de Usuario de Compra',
      'buttons' => 
      array (
        'verify' => 'Verificar Código',
      ),
    ),
  ),
  'environment' => 
  array (
    'menu' => 
    array (
      'templateTitle' => 'Paso 3 | Configuración del Entorno',
      'title' => 'Configuración del Entorno',
      'desc' => 'Selecciona cómo deseas configurar el archivo <code>.env</code> de la aplicación.',
      'wizard-button' => 'Configuración Asistida por Formulario',
      'classic-button' => 'Editor de Texto Clásico',
    ),
    'wizard' => 
    array (
      'templateTitle' => 'Paso 3 | Configuración del Entorno | Asistente Guiado',
      'title' => 'Asistente Guiado para <code>.env</code>',
      'tabs' => 
      array (
        'environment' => 'Entorno',
        'database' => 'Base de Datos',
        'application' => 'Aplicación',
      ),
      'form' => 
      array (
        'name_required' => 'Se requiere un nombre de entorno.',
        'app_name_label' => 'Nombre de la Aplicación',
        'app_name_placeholder' => 'Nombre de la Aplicación',
        // ... (other translations)
      ),
    ),
  ),
);
