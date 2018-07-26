import app from '../app'

app.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) => {
  $urlRouterProvider.otherwise('/app/home');

  $stateProvider
    .state('app', {
      abstract: true,
      url: '/app',
      templateUrl: 'public/templates/app.html'
    })
    .state('app.home', {
      url: '/home',
      templateUrl: 'public/templates/application/home.html'
    })
    .state('app.doctors', {
      url: '/doctors',
      templateUrl: 'public/templates/application/doctors.html',
      controller  : 'DoctorsController',
      resolve: {
        doctors: ['Restangular', function(Restangular) {
          return Restangular.all('doctors').getList();
        }]
      }
    })
    .state('app.entidades', {
      url: '/entidades',
      templateUrl: 'public/templates/application/entidades.html',
      controller  : 'EntidadesController',
      resolve: {
        entidades: ['Restangular', function(Restangular) {
          return Restangular.all('entidades').getList();
        }]
      }
    })
    .state('app.personals', {
      url: '/empleados',
      templateUrl: 'public/templates/application/personals.html',
      controller  : 'PersonalsController',
      resolve: {
        personals: ['Restangular', function(Restangular) {
          return Restangular.all('empleados').getList();
        }],
        sucursales: ['Restangular', function(Restangular) {
          return Restangular.all('sucursales').getList();
        }],
      }
    })
    .state('app.proveedores', {
      url: '/proveedores',
      templateUrl: 'public/templates/application/proveedores.html',
      controller  : 'ProveedoresController',
      resolve: {
        proveedores: ['Restangular', function(Restangular) {
          return Restangular.all('proveedores').getList();
        }]
      }
    })
    .state('app.sucursales', {
      url: '/sucursales',
      templateUrl: 'public/templates/application/sucursales.html',
      controller  : 'SucursalesController',
      resolve: {
        sucursales: ['Restangular', function(Restangular) {
          return Restangular.all('sucursales').getList();
        }]
      }
    })
    .state('app.pacientes', {
      url: '/pacientes',
      templateUrl: 'public/templates/application/pacientes.html',
      controller  : 'PacientesController',
      resolve: {
        pacientes: ['Restangular', function(Restangular) {
          return Restangular.all('pacientes').getList();
        }],
        entidades: ['Restangular', function(Restangular) {
          return Restangular.all('entidades').getList();
        }]
      }
    })
    .state('app.planillas', {
      url: '/planillas',
      templateUrl: 'public/templates/application/planillas.html',
      controller  : 'PlanillaController',
      resolve: {
        yearPlanilla: ['Restangular', function(Restangular) {
          return Restangular.all('planillas').customGET('yearPlanillas');
        }]
      }
    })
    .state('app.permisos', {
      url: '/permisos',
      templateUrl: 'public/templates/application/permisos.html',
      controller  : 'PermisosController',
      resolve: {
        object: ['Restangular', function(Restangular) {
          return Restangular.all('permisos').customGET('all');
        }]
      }
    })
    .state('app.compras', {
      url: '/compras',
      templateUrl: 'public/templates/application/compras.html',
      controller  : 'ComprasController',
      resolve: {
         comprasList: ['Restangular', function(Restangular, $stateParams) {
          return Restangular.all('compras').getList();
        }],
      }
    })
    .state('app.comprasnueva', {
      url: '/compra/{proveedor_id}/nueva',
      controller: 'NuevaComprasController',
      templateUrl: 'public/templates/application/nueva.compras.html',
      resolve: {
        productosItems: ['Restangular', '$stateParams' ,function(Restangular, $stateParams) {
          return Restangular.all('proveedores/' + $stateParams.proveedor_id).customGET('materiales')
        }],
        proveedor: ['$stateParams', 'Restangular' ,function($stateParams, Restangular) {
          return Restangular.all('proveedores').customGET($stateParams.proveedor_id)
        }],
      }
    })
    .state('app.comprasdetalles', {
      url: '/compra/{compra_id}/detalles',
      controller: 'DetallesComprasController',
      templateUrl: 'public/templates/application/detalles.compras.html',
      resolve: {
        compra: ['Restangular', '$stateParams' , function(Restangular, $stateParams) {
          return Restangular.all('compras').customGET($stateParams.compra_id);
        }],
        items: ['Restangular', '$stateParams' , function(Restangular, $stateParams) {
          return Restangular.all('compras/' + $stateParams.compra_id ).customGET('items');
        }],
      }
    })
    .state('app.insumos_reactivos', {
      url: '/materiales',
      templateUrl: 'public/templates/application/catalogo-materiales.html',
      controller  : 'InsuReactivosController',
      resolve: {
        materiales: ['Restangular', function(Restangular) {
          return Restangular.all('materiales').customGET("");
        }]
      }
    })
    .state('app.insumos_reactivos.item', {
      url: '/{id}/item',
      resolve: {
        object : ['Restangular', '$stateParams' , function(Restangular, $stateParams) {
          return Restangular.all('materiales').customGET($stateParams.id);
        }]
      },
      views : {
        'item' : {
          templateUrl : 'public/templates/application/catalogo-materiales-item.html',
          controller  : 'ItemReactivoController'
        }
      }
    })
    // routers for usuarios
    .state('app.usuarios', {
      url: '/usuarios',
      templateUrl: 'public/templates/application/views.usuarios.html',
      controller  : 'UsuariosControllers'
    })
    .state('app.usuarios.recursos', {
      url: '/recursos',
      views:{
        'item' : {
          templateUrl: 'public/templates/application/views.recursos.html',
          controller  : 'RecursosControllers',
        }
      },
      resolve: {
        objectList : ['Restangular', function(Restangular) {
          return Restangular.all('users').customGET('recursos');
        }]
      }
    })
    .state('app.usuarios.users', {
      url: '/users',
      views : {
        'item' : {
          templateUrl: 'public/templates/application/views.user.html',
          controller  : 'UsuarioControllers',
        }
      },
      resolve: {
        objectList : ['Restangular', function(Restangular) {
          return Restangular.all('users').customGET('list');
        }],
        sucursales: ['Restangular', function(Restangular) {
          return Restangular.all('sucursales').getList();
        }],
        perfiles: ['Restangular', function(Restangular) {
          return Restangular.all('users/perfiles').getList();
        }]
      }
    })
    .state('app.usuarios.perfiles', {
      url: '/perfiles',
      views : {
        'item' : {
          templateUrl: 'public/templates/application/views.perfiles.html',
          controller  : 'PerfilesControllers',
        }
      },
      resolve: {
        objectList : ['Restangular', function(Restangular) {
          return Restangular.all('users').customGET('perfiles');
        }]
      }
    })
    .state('app.usuarios.perfil', {
      url: '/{id}/perfil',
      views : {
        'item' : {
          templateUrl: 'public/templates/application/views.perfil.html',
          controller  : 'PerfilControllers',
        }
      },
      resolve: {
        objectData : ['Restangular', '$stateParams' ,function(Restangular, $stateParams) {
          return Restangular.all("users/perfiles").customGET($stateParams.id)
        }]
      }
    })
    .state('app.inventario', {
      url: '/inventario',
      templateUrl: 'public/templates/application/inventario.html',
      controller  : 'InventarioController',
      resolve: {
         inventario: ['Restangular', function(Restangular) {
          return Restangular.all('inventario').customGET('');
        }],
      }
    })
    .state('app.examenes', {
      url: '/examenes',
      templateUrl: 'public/templates/application/categoria.examen.html',
      controller  : 'ExamenController',
      resolve: {
        categorias: ['Restangular', function(Restangular) {
         return Restangular.all('categoria_examenes').customGET('');
        }],
      }
    })
    .state('app.catalogosolicitud', {
      url: '/catalogosolicitud',
      templateUrl: 'public/templates/application/solicitud.view.controller.html',
      controller: "SolicitudesItemController",
      resolve: {
        object: ['Restangular', function(Restangular) {
          return Restangular.all('solicitud').customGET('all');
        }],
      }
    })
    .state('app.catalogosolicitud.item', {
      url: '/{id}/item',
      views: {
        'item': {
          templateUrl: 'public/templates/application/solicitud.view.item.controller.html',
          controller: "SolicitudItemController",
        }
      },
      resolve: {
        object: ['Restangular', '$stateParams', function(Restangular, $stateParams) {
          return Restangular.all('solicitud/'+$stateParams.id).customGET('all')
        }],
      }
    })
    .state('app.examenes.item', {
      url: '/examenes/{idcategoria}',
      views : {
        'item' : {
          templateUrl: 'public/templates/application/categoria.examen.item.html',
          controller  : 'ExamenItemController',
        }
      },
      resolve: {
        objectData : ['Restangular', '$stateParams' ,function(Restangular, $stateParams) {
          return Restangular.all("categoria_examenes").customGET($stateParams.idcategoria + '/examenes')
        }]
      }
    })
    .state('app.examenes.examen', {
      url: '/{id}/examen',
      views : {
        'item' : {
          templateUrl: 'public/templates/application/views.examen.examen.html',
          controller  : 'ExamenViewController',
        }
      },
      resolve: {
        objectData : ['Restangular', '$stateParams' ,function(Restangular, $stateParams) {
          return Restangular.all("examenes/").customGET($stateParams.id + '/materiales')
        }],
        campos : ['Restangular', '$stateParams' ,function(Restangular, $stateParams) {
          return Restangular.all("examenes/").customGET($stateParams.id + '/campos')
        }],
      }
    })
    .state('app.promociones', {
      url: '/promociones',
      templateUrl: 'public/templates/application/promociones.html',
      controller: 'PromocionController',
      resolve: {
        objectData : ['Restangular', function(Restangular) {
          return Restangular.all("promocion").customGET()
        }]
      }
    })
    .state('app.catalogocampos', {
      url: '/catalogocampos',
      templateUrl: "public/templates/application/catalogo-campos.html",
      controller: "CatalogoCampoController",
      resolve: {
        catalogo : ['Restangular', function(Restangular) {
          return Restangular.all("catalogoCampos").customGET();
        }]
      }
    })
    .state('app.catalogocampos.item', {
      url: '/{typecampo}/item',
      views : {
        'item': {
          templateUrl: "public/templates/application/catalogo-campos-item.html",
          controller: "CatalogoItemCampoController",
        }
      },
      resolve: {
        object: ['Restangular', '$stateParams', function(Restangular, $stateParams) {
          return Restangular.all('catalogoCampos').customGET($stateParams.typecampo)
        }],
        objectCategoria: ['Restangular', function(Restangular) {
          return Restangular.all('objectCategoria').customGET()
        }]
       }
    })
    .state('app.solicitud', {
      url : '/solicitud/{type}/paciente/{id_paciente}',
      templateUrl : 'public/templates/application/solicitud.paciente.html',
      controller: 'SolicitudPacienteController',
      resolve: {
        paciente: ['Restangular', '$stateParams', function(Restangular, $stateParams) {
          return Restangular.all('pacientes').customGET($stateParams.id_paciente)
        }],
        type: ['Restangular', '$stateParams', function(Restangular, $stateParams) {
          if($stateParams.type == '1'){
            return Restangular.all('promocion').customGET('', {
              estado: 1
            });
          }else if($stateParams.type == '2'){
            return Restangular.all('categoria_examenes').customGET('');
          }
          return null;
        }]
      }
    })
    .state('app.solicitud_entidad', {
      url : '/solicitud/paciente/{id_paciente}/entidad/{id_entidad}',
      templateUrl : 'public/templates/application/solicitud.entidad.html',
      controller: 'SolicitudEntidadController',
      resolve: {
        paciente: ['Restangular', '$stateParams', function(Restangular, $stateParams) {
          return Restangular.all('pacientes').customGET($stateParams.id_paciente)
        }],
        entidad: ['Restangular', '$stateParams', function(Restangular, $stateParams) {
          return Restangular.all('entidad/' + $stateParams.id_entidad).customGET('examenes/monto')
        }]
      }
    })
    .state('app.promociones.item', {
      url: '/promociones/{id}',
      views : {
        'item' : {
          templateUrl: 'public/templates/application/promocion.item.html',
          controller  : 'PromocionItemController',
        }
      },
      resolve: {
         objectData : ['Restangular', '$stateParams', function(Restangular, $stateParams) {
          return Restangular.all("promocion").customGET($stateParams.id)
        }]
      }
    })

    // routes for people perfil
    .state('app.perfil', {
      url : "/perfil",
      templateUrl: "public/templates/application/perfil.html"
    })
    .state('app.perfil.data', {
      url : "/informacion",
      views: {
        "item": {
          templateUrl: "public/templates/application/perfil.paciente.html",
          controller: 'PerfilController',
        }
      },
      resolve: {
        perfil: ["Restangular", function(Restangular) {
          return Restangular.all('perfil').customPOST({ }, '');
        }]
      }
    })
    .state('app.perfil.domicilio', {
      url: "/perfil/solicitud",
      views: {
        item: {
          templateUrl: "public/templates/application/solicitud.adomicilio.html",
          controller: "SolicitudDomicilioPerfilController"
        }
      },
      resolve: {
        sucursales: ["Restangular", (Restangular) => {
          return Restangular.all('sucursals').customGET('PuntsGeograficos')
        }],
        examenes: ["Restangular", function(Restangular) {
          return Restangular.all('examenes').customGET('all')
        }]
      }
    })
    // route for perfil personal
    .state('app.changePassword',{
      url: '/perfil/changepassword',
      templateUrl: 'public/templates/application/perfil.changepassword.html',
      controller: 'PerfilChangePassword',
      resolve: {
        perfil: ["Restangular", function(Restangular) {
          return Restangular.all('changepassword').customPOST({ }, 'perfil');
        }]
      }
    })
    .state('app.rentaconfiguration', {
      url: "/rentaconfiguration",
      templateUrl: "public/templates/application/renta.configuration.html",
      controller: "RentaConfiguration",
      resolve: {
        rentaConfiguration: ["Restangular", function(Restangular) {
          return Restangular.all('renta').customGET('configuration');
        }],
        'porcentajes': ["Restangular", function(Restangular) {
          return Restangular.all('renta').customGET('correlativos');
        }],
        'horarios': ["Restangular", function(Restangular) {
          return Restangular.all('horarios/all').customGET();
        }]
      }
    })
    .state('app.solicitudDomicilio', {
      url: "/solicitudDomicilio",
      templateUrl: "public/templates/application/solicitud.domicilio.html",
      controller: "SolicitudDomicilioController",
      resolve: {
        "object": ["Restangular", function(Restangular) {
          return Restangular.all('solicitudes/admicilio').customGET();
        }]
      }
    })
    .state('app.solicitudDomicilio.item', {
      url: "/{id}",
      views: {
        'item': {
          templateUrl: "public/templates/application/solicitud.domicilio.item.html",
          controller: "SolicitudDomicilioItemController"
        }
      },
      resolve: {
        "object": ["Restangular", "$stateParams", function(Restangular, $stateParams) {
          return Restangular.all(`solicitudes/${$stateParams.id}/admicilio`).customGET();
        }]
      }
    })
    .state('app.perfil.cita', {
      url: "/citas",
      "views" : {
        "item": {
          templateUrl: "public/templates/application/citas.html",
          controller: "ReservarCitaController",
        }
      },
      resolve: {
        "object": ["Restangular", function(Restangular) {
          return Restangular.all('citas').customPOST({}, '')
        }]
      }
    })
    .state('app.reajustar', {
      url: '/inventario/{id}/reajustar',
      controller: 'InventarioReajustarController',
      templateUrl: 'public/templates/application/reajustar.inventario.html',
      resolve: {
        object: ['Restangular', '$stateParams', function (Restangular, $stateParams) {
          return Restangular.all('inventario/' + $stateParams.id ).customGET('item')
        }]
      }
    })
    .state('app.citas', {
      url: '/citas',
      controller: 'CitaController',
      templateUrl: 'public/templates/application/citas.administration.html'
    })
    .state('app.bitacora', {
      url: '/bitacora',
      controller: 'BitacoraController',
      templateUrl: 'public/templates/application/bitacora.html',
      resolve: {
        'object' : ["Restangular", function(Restangular){
          return Restangular.all('bitacora/all').customGET()
        }]
      }
    })
    .state('app.noticias', {
      url: '/noticias',
      controller: 'NoticiaController',
      templateUrl: 'public/templates/application/noticias.html',
      resolve: {
        'noticias' : ["Restangular", function(Restangular) {
          return Restangular.all('noticias').customGET();
        }]
      }
    })
    .state('app.finanzas', {
      url: '/finanzas',
      controller: 'FinanzaController',
      templateUrl: 'public/templates/application/finanzas.html',
      resolve: {
        'entidades' : ["Restangular", function(Restangular) {
          return Restangular.all('entidades').customGET();
        }]
      }
    })
    .state('app.perfil.domiciliohome', {
      url: "/solicitud/home",
      "views" : {
        "item": {
          controller: "SolicitudDomicilioHomeController",
          templateUrl: "public/templates/application/solicitud.adomicilio.home.html"
        }
      },
      resolve: {
        arrayOfList: ["Restangular", (Restangular) => {
          return Restangular.all('solicitudes/admicilio').customGET('perfil')
        }]
      }
    })
    .state('app.pacientecita', {
      url: "/pacientecita"
    })
}])
