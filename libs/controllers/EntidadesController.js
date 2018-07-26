import { filter } from 'lodash'
//import $ from 'jQuery'

module.exports = function($scope, $rootScope, entidades, Restangular, $uibModal) {
  $scope.items = entidades
  $scope.item  = entidades[0]
  $scope.item.selected = true
  $scope.permisos = filter($rootScope.recursos, { url: 'entidades' })[0]

  $scope.selectItem = (item) =>{
    angular.forEach($scope.items, (i, index) => {
      i.selected  = false
      i.editing   = false
    })
    $scope.item = item
    $scope.item.selected = true
  }

  $scope.createItem = () => {
    let item = { status: 1, isNew: true }
    $scope.items.push(item)
    $scope.selectItem(item)
    $scope.item.editing = true
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
      Restangular.all('entidades').post(item).then((element) => {
        $scope.items.pop()
        $scope.items.push(element)
        $scope.selectItem(element)
      });
    }else{
      Restangular.all('entidad').customPOST({
        item,
      }).then((it) => {
        item.editing = false
      })
    }
  }

  $scope.addMonto = (item) => {
    let modalMonto = $uibModal.open({
      resolve: {
        monto: ['Restangular', function(Restangular){
          return Restangular.all('entidad/' + item.id).customGET('monto');
        }],
        entidad : item
      },
      templateUrl : 'public/templates/modals/agregar.examen.monto.html',
      controller: ($scope, $uibModalInstance, monto, entidad, Restangular, toastr) => {
        $scope.entidad = entidad;
        $scope.monto = monto.data ? monto.data : { monto : 0.00 };

        $scope.onClose = () => { $uibModalInstance.close({  }); }

        $scope.addMonto = () => {
          Restangular.all('entidad/add').customPOST({
            id_entidad  : $scope.entidad.id,
            monto       : $scope.current_monto
          }, 'monto').then((response) => {
            $scope.current_monto = null;
            $scope.monto = response.data;
            toastr.success(response.message, 'Exito!');
          }, (error) => {
            toastr.error(error.data.message, 'Error!');
          });
        }
      }
    });
  }

  /* funciones para el agregado de examenes a las entidades  */
  $scope.addExamen = (item) => {
    let modal = $uibModal.open({
      resolve: {
        objectData: ['Restangular', function(Restangular){
          return Restangular.all('entidad/' + item.id).customGET('examen');
        }]
      },
      size: 'noventa',
      templateUrl: 'public/templates/modals/agregar.examen.entidad.html',
      controller: ($scope, objectData, $uibModalInstance, toastr, $rootScope) => {
        $scope.permisos = filter($rootScope.recursos, { url: 'entidades' })[0]

        function init() {
          $scope.list = [];
          $scope.server = false;
          $scope.itemElement = null;
          $scope.searchEmployeer = null;
        }
        init();

        $scope.objectData = objectData.data;
        $scope.cerrar = () => { $uibModalInstance.close({}); };

        $scope.onSelectExamen = (item_examen) => {
          $scope.itemElement = item_examen;
        }

        $scope.cancelar = () => {
          init();
        }

        $scope.onCreateExamen = () => {
          if($scope.itemElement){
            Restangular.all('entidad/add/examen').customPOST({
              id_entidad : $scope.objectData.id,
              id_examen  : $scope.itemElement.id,
              precio     : $scope.itemElement.precio
            }, '').then((response) => {
              toastr.success(response.message, 'Exito!');
              $scope.objectData.examenes.unshift(response.data);
              init();
            }, (error) => {
              init();
              toastr.error(error.data.message, 'Problema!');
            })
          }
        }

        $scope.onSearchExamenes = () => {
          if($scope.searchEmployeer){
            Restangular.all('promocion/search').customGET('', {
              q: $scope.searchEmployeer
            }).then((response) => {
              $scope.list = response.data;
              $scope.server = response.server;
            })
          }
        }

        $scope.onDelete = ($index, element) => {
          if(element){
            Restangular.all('entidad/examen').customPOST({
              id: element.id
            }, 'delete').then((response) => {
              toastr.success(response.message, 'Exito!');
              $scope.objectData.examenes.splice($index, 1);
            }, (error) => {
              toastr.error(error.data.message, 'Problema!');
            })
          }
        }
      }
    });
  }
}
