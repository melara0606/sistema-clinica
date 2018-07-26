import { filter, groupBy, map, remove, includes } from 'lodash'
require('angular-i18n/es-sv')

module.exports = function($scope, $rootScope, pacientes, entidades, Restangular, $uibModal, $state, toastr) {
  $scope.querySuc = -1
  $scope.items = pacientes
  $scope.item  = pacientes[0]
  $scope.item.selected = true
  $scope.item.date_pac = new Date(pacientes[0].date_pac);
  $scope.entidades = entidades;


  $scope.permisos = filter($rootScope.recursos, { url: 'pacientes' })[0]

  $scope.changePassword = (item) => {
    Restangular.all('admin/forgotpwd').customPOST({
      id: item.id
    }, 'paciente').then(json => {
      toastr.success('Hemos restaurando la contraseña a la inicial: 0123456789', 'Exito');
    })
  }
  $scope.openPaciente = (item) => {
    let modal = $uibModal.open({
      templateUrl : 'public/templates/modals/solicitud.catalogo.paciente.html',
      resolve: {
        entidad: ['Restangular', function(Restangular) {
          return Restangular.all('entidades/' + item.entidad_id ).customGET('');
        }],
        paciente: item
      },
      controller: function($scope, entidad, paciente, $uibModalInstance, $state) {
        $scope.close = ()  => { $uibModalInstance.close({ }); };
        $scope.entidad = entidad;

        $scope.crearSolicitud = (valid) => {
          if(valid){
            if($scope.type_solicitud == 1 || $scope.type_solicitud == 2){
              $state.go('app.solicitud', {
                type: $scope.type_solicitud,
                id_paciente: paciente.id
              });
            }else{
              $state.go('app.solicitud_entidad', {
                id_entidad: $scope.entidad.id,
                id_paciente: paciente.id
              });
            }
            $uibModalInstance.close({ });
          }
        }
      }
    });
  };

  $scope.openCitaRapida = (item) => {
    let entidad = filter(entidades, {
      id: item.entidad_id
    })

    let modal = $uibModal.open({
      size: 'lg',
      resolve: {
        entidad: [function() {
          return entidad[0]
        }],
        item: item
      },
      templateUrl: 'public/templates/modals/paciente.cita.html',
      controller: ($scope, $uibModalInstance, $state, entidad, $uibModal, toastr, item) => {
        $scope.selectShow = !!(entidad.view)
        // TimePicker        
        $scope.hstep = $scope.hstep2 = 1;
        $scope.mstep = $scope.mstep2 = 5;

        $scope.ismeridian = true;
        $scope.myBegid = $scope.myEnd = new Date();

        // DatePicker
        $scope.today = function() {
          $scope.dt = new Date();
        };
        $scope.today();

        $scope.dateOptions = {
          formatYear: 'yy',
          maxDate: new Date(2020, 5, 22),
          minDate: new Date(),
          startingDay: 1
        };

        $scope.open1 = function() {
          $scope.popup1.opened = true;
        };

        $scope.setDate = function(year, month, day) {
          $scope.dt = new Date(year, month, day);
        };

        $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
        $scope.format = $scope.formats[0];
        $scope.altInputFormats = ['M!/d!/yyyy'];

        $scope.popup1 = {
          opened: false
        };

        $scope.close = () => { $uibModalInstance.close({ }) }
        $scope.crearSolicitud = function () {
          var scope = $scope;
          if(scope.selectShow && !scope.type_solicitud){
            toastr.error('Debes seleccionar un tipo de cita', 'Error!')
            return;
          }
          if(scope.myBegid >= scope.myEnd){
            toastr.error('Debes seleccionar un horario adecuado', 'Error!')
            return;
          }
          $uibModalInstance.close({ })
          let modalTwo = $uibModal.open({
            size: 'noventa',
            resolve: {
              'categorias'  : ['Restangular', function (Restangular) {
                if(scope.type_solicitud && scope.type_solicitud == '1') {
                  return Restangular.all('categoria_examenes').customGET(`${entidad.id}/entidad`)
                }else{
                  return Restangular.all('categoria_examenes').customGET()
                }
              }],
              parentScope: scope
            },
            templateUrl: 'public/templates/modals/agregar.citas.html',
            controller: ($scope, toastr, categorias, parentScope, $uibModalInstance) => {
              $scope.dineroEntidad = true;
              $scope.parentScope = parentScope
              $scope.categorias = categorias.data
              $scope.cerrar = () => $uibModalInstance.close({  })
              $scope.itemExamens = { show: false, item: [], ids: [], pagar: 0.00 }
              $scope.isViewFalseAndTrue = (!parentScope.selectShow) ? true : (parentScope.type_solicitud == '2') ? true : false

              if(!$scope.isViewFalseAndTrue){
                $scope.categorias.forEach( i =>{
                  i.value = $scope.itemExamens.ids.includes(i.id)
                  $scope.itemsListExamen = $scope.categorias
                })
                $scope.dineroEntidad = categorias.response
              }

              $scope.onCreateCode = () => {
                let objectData = {
                  estado: 1,
                  perfil: false,
                  paciente: item.id,
                  fecha: parentScope.dt,
                  entidad: item.entidad_id,
                  horarioEnd: parentScope.myEnd,
                  horarioBegin: parentScope.myBegid,
                  itemsListExamen: $scope.itemExamens,
                  tipoCita: $scope.isViewFalseAndTrue ? 1 : 2,
                }
                Restangular.all('citas/add').customPOST(objectData, 'paciente').then((json) => {
                  toastr.success(json.message, 'Exito!')
                  $uibModalInstance.close({  })
                }, (error) => {
                  toastr.success(error.error.message, 'Error!')
                })
              }
              // Funciones del controlador
              $scope.changeCategoria = () => {
                Restangular.all(`categoria_examenes/${$scope.categoriaId}/`).customGET('examenes').then( json => {
                  let data = json.data.examenes
                  data.forEach(i => {
                    i.value = $scope.itemExamens.ids.includes(i.id)
                  })
                  $scope.itemsListExamen = data
                });
              }
              function removeElement(element) {
                remove($scope.itemExamens.item, item => Object.is(element.id, item.id) )
                remove($scope.itemExamens.ids,  item => Object.is(element.id, item) )
                $scope.itemExamens.pagar -= element.precio
                
                $scope.itemsListExamen.forEach(item => {
                  if(item.id === element.id){
                    item.value = false
                  }
                })
              }

              $scope.itemDelete = (element, $index) => {
                removeElement(element)
                mostrarFormulario()
              }

              $scope.onValueData = (element) => {
                if(element.value){
                  $scope.itemExamens.item.push(element)
                  $scope.itemExamens.ids.push(element.id)
                  $scope.itemExamens.pagar += element.precio
                }else{
                  removeElement(element)
                }
                mostrarFormulario()
              }
              function mostrarFormulario() {
                $scope.itemExamens.show = $scope.itemExamens.item.length > 0
              }
            }
          })
        }
      }
    })
  }

  $scope.getEntidad = (entidadId) => {
    if(entidadId){
      return filter(entidades, { id: entidadId })[0]["name_ext"];
    }
  }

  $scope.createItem = () => {
    let item = { status: 1, isNew: true, date_pac: new Date() }
    $scope.items.push(item)
    $scope.selectItem(item)
    $scope.item.editing = true
  }

  $scope.selectItem = (item) =>{
    angular.forEach($scope.items, (i, index) => {
      i.selected  = false
      i.editing   = false
    });

    item.date_pac = new Date(item.date_pac)
    $scope.item = item
    $scope.item.selected = true
  }

  $scope.editItem = (item) => {
    if(item && item.selected){
      item.editing = true
      item.isNew = false
    }
  }

  $scope.deleteItem = (item) =>{
    if(item.id && !item.editing){
      item.post('delete', item).then((res) => {
        item.status = !item.status
      })
    }else if(item.isNew){
      $scope.items.pop()
      $scope.item = $scope.items[0]
      $scope.item.selected = true
    }else{
      item.editing = false
    }
  }

  $scope.doneEditing = (item) => {
    if(item.isNew){
      Restangular.all('pacientes').post(item).then((element) => {
        $scope.items.pop()
        $scope.items.push(element)
        $scope.selectItem(element)
        
      });
    }else{
      item.post().then((it) => {
        item.editing = false
      })
    }
  }

  $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
  $scope.format = $scope.formats[0];
  $scope.altInputFormats = ['M!/d!/yyyy'];

  $scope.popup1 = {
    opened: false
  };

  $scope.dateOptions = {
    formatYear: 'yy',
    startingDay: 1,
    class: 'datepicker',
    minDate: new Date(1900, 1, 1)
  }

  $scope.today = function() {
    $scope.dt = new Date();
  };
  $scope.today();

  $scope.open1 = function() {
    $scope.popup1.opened = true;
  };

  $scope.openModalDateBeginAndEnd = (item) => {
    let modal = $uibModal.open({
      templateUrl: 'public/templates/modals/rango-fechas.html',
      controller: ($scope, $uibModalInstance, toastr) => {
        $scope.cerrar = () => $uibModalInstance.close({ result: false })
        // Configuracion para el calendario
        $scope.dateend = new Date();
        $scope.datebegin = new Date();
        $scope.format = 'dd-MMMM-yyyy';
        $scope.popup1 = { opened: false };
        $scope.popup2 = { opened: false };
        $scope.altInputFormats = ['M!/d!/yyyy'];

        $scope.open1 = () => { $scope.popup1.opened = true;  };
        $scope.open2 = () => { $scope.popup2.opened = true;  };

        /* Evento para el submit */
        $scope.onValidateRango = () => {
          if($scope.datebegin >= $scope.dateend ){
            toastr.error('Lo sentimos pero la fecha de inicio no debe ser mayor o igual a la fecha final', 'Error!')
          }else{
            $uibModalInstance.close({ 
              result: true, 
              url: 'reporte/solicitudes/paciente',
              begin: $scope.datebegin,
              end  : $scope.dateend
            })
          }
        }
      }
    })

    modal.result.then( response => {
      if(response.result){
        let { begin, end, url} = response
        let endDate   = `${ end.getUTCFullYear() }-${ end.getUTCMonth() + 1 }-${ end.getUTCDate() }`
        let beginDate = `${ begin.getUTCFullYear() }-${ begin.getUTCMonth() + 1 }-${ begin.getUTCDate() }`
        window.open(`${ url }?b=${beginDate}&f=${endDate}&paciente=${item.id}`, '_blank')
      }
    })
  }

  $scope.onKeypress = (keyEvent) => {
    if(keyEvent.which === 13){
      $scope.query = "";
      Restangular.all('pacientes').customGET()
        .then(json => {
          generador($scope, json)
        })
    }
  }

  $scope.$watch('query', function (value) {
    if(value && value.length >= 3){
      Restangular.all('pacientes/searchQuery?q='+ value).customGET()
      .then(json => {
        if(json.data.length > 0){
          generador($scope, json.data)
        }else{
          $scope.items = [];
        }
      })
    }
  })

   function generador($scope, data){
    $scope.items = data;
    $scope.item  = $scope.items[0]
    $scope.item.selected = true
  }
}
