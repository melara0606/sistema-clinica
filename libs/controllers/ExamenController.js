import filter from 'lodash/filter'
import map from 'lodash/map'
// import 'jquery-ui'
// import 'jquery'

module.exports = function($scope, $rootScope, categorias, $uibModal) {
  $scope.categorias = categorias.data;
  $scope.permisos = filter($rootScope.recursos, { url: 'examenes' })[0];

  $scope.open = (isEdit, categoria = {}, $index) => {

    if($scope.permisos.agregar || $scope.permisos.editar){
      var modalInstance = $uibModal.open({
        templateUrl: 'public/templates/modals/agregar.categoria.examen.html',
        resolve: {
          isEdit: isEdit,
          categoria: categoria
        },
        controller: ($scope, $uibModalInstance, isEdit, Restangular, toastr, categoria) => {
          $scope.isEdit = isEdit;
          $scope.editing = false;
          $scope.categoria = categoria;
          $scope.cerrar = () => { $uibModalInstance.dismiss('cancel'); }

          $scope.onUpdateCode = () => {
            if(!$scope.isEdit){
              Restangular.all('categoria_examenes').customPOST({
                compra: $scope.categoria
              }, '').then(function(response) {
                toastr.success(response.message, 'Exito!');
                $uibModalInstance.close({ categoria: response, isEdit: $scope.isEdit });
              });
            }else {
              Restangular.all('categoria_examenes').customPOST({
                compra: $scope.categoria
              }, $scope.categoria.id).then(function(response) {
                toastr.success(response.message, 'Exito!');
                $uibModalInstance.close({ categoria: response, isEdit: $scope.isEdit });
              }, function(error) {
                toastr.error(response.message, 'Problema!');
              });
            }
          }
        }
      });

      modalInstance.result.then((response) => {
        let { categoria, isEdit } = response;

        if(isEdit){
          console.log($index, $scope.categorias[$index])
          $scope.categorias[$index].nombre_categoria = categoria.nombre_categoria;
        }else{
          $scope.categorias.unshift({
            nombre_categoria: categoria.nombre_categoria,
            id: categoria.id
          });
        }
      });
    }
	}
}

module.exports.item = function($scope, $rootScope, objectData, toastr, Restangular) {
  $scope.categoria = objectData.data;
  $scope.examenes = objectData.data.examenes;
  $scope.editing = false;
  $scope.userNew = {};
  $scope.permisos = filter($rootScope.recursos, { url: 'examenes' })[0];

  $scope.onCreate = () => {
    $scope.editing = true;
    $scope.userNew = {};
  }

  $scope.editExamen = (item, $index) => {
    $scope.userNew = item;
    $scope.editing = true;
    $scope.$index = $index;
  }

  $scope.onCreateUser = ($valid) => {
    if($valid){
      if($scope.userNew.id){
        Restangular.all('edit_examens/' + $scope.userNew.id).customPOST({
          nombre_examen: $scope.userNew.nombre_examen,
          precio      : $scope.userNew.precio
        }).then((response) => {
          $scope.editing = false;
          $scope.examenes[$scope.$index] = response;
          toastr.success(response.message, 'Exito!');
        });
        return;
      }

      Restangular.all('add_examenes').customPOST({
        nombre_examen: $scope.userNew.nombre_examen,
        categoria_id:  $scope.categoria.id,
        precio      : $scope.userNew.precio
      }).then(function(response) {
        $scope.editing = false;
        $scope.examenes.unshift({
          categoria_id: response.categoria_id ,
          nombre_examen: response.nombre_examen,
          precio: response.precio,
          id: response.id
        });

        toastr.success(response.message, 'Exito!');
      }, function(error) {
        toastr.error(error.message, 'Problema!');
      });
    }
  }
}

module.exports.ViewController = ($scope, $rootScope, objectData, campos,  toastr, Restangular, $uibModal) => {
  $scope.campos = campos.data;
  $scope.data = objectData.data;
  $scope.permisos = filter($rootScope.recursos, { url: 'examenes' })[0];

  $scope.sortableOptions = {
    update: function(e, ui) {}
  };

  $scope.saveOrdenCampos = (event) => {
    let ids = map($scope.campos, j => j.id)
    Restangular.all('examen').customPOST({
      ids
    }, 'ordenCampos').then(response => {
      toastr.success(response.mensaje, 'Exito');
    })
  }

  $scope.open = () => {
    var modalInstance = $uibModal.open({
      templateUrl: 'public/templates/modals/agregar.examen.material.html',
      resolve:{
        id: $scope.data.id,
        ctr: $scope
      },
      controller: function($scope, id, toastr, ctr, $uibModalInstance) {
        $scope.list = [];
        $scope.server = false;

        $scope.onClose = () => { $uibModalInstance.close({}); };

        $scope.onCreateMaterial = () => {
          Restangular.all('examenes/add/materiales').customPOST({
            id_examen: id,
            id_material: $scope.itemElement.id,
            uso: parseInt($scope.itemElement.cantidad)
          }, '').then((response) => {
            toastr.success(response.message, 'Exito!');
            ctr.data.materiales.unshift(response.data);
          }, function(error) {
            toastr.error(error.data.message, 'Problema!');
          });
          $scope.btnCancelar();
        };

        function init() {
          $scope.list = [];
          $scope.server = false;
          $scope.searchMaterial = null;
        }

        init();

        $scope.itemElement = null;
        $scope.onSelectMaterial = (item) => {
          $scope.itemElement = item;
        };

        $scope.btnCancelar = () => {
          $scope.itemElement = null;
          init();
        }

        $scope.onSearchMaterial = () => {
          if($scope.searchMaterial){
            Restangular.all('search/materiales').customGET('', {
              query: $scope.searchMaterial
            }).then((response) => {
              $scope.list = response.data;
              $scope.server = true;
            });
          }
        };
      }
    })
  }

  $scope.openCampo = () => {
    var modalInstance = $uibModal.open({
      templateUrl: 'public/templates/modals/agregar.examen.campo.html',
      resolve:{
        id: $scope.data.id,
        ctr: $scope
      },
      controller: function($scope, id, toastr, ctr, $uibModalInstance) {
        $scope.list = [];
        $scope.server = false;
        $scope.ctr = ctr;

        $scope.onClose = () => { $uibModalInstance.close({}); };

        function init() {
          $scope.list = [];
          $scope.server = false;
          $scope.searchMaterial = null;
        }

        init();

        $scope.itemElement = null;

        $scope.onSelectAdd = (item) => {
          Restangular.all('examenes/add/campo').customPOST({
            catalogo_campo_id: item.id,
            examen_id: ctr.data.id,
          }, '').then((response) => {
            toastr.success(response.message, 'Exito!');
            ctr.campos.unshift(response.data[0]);
          }, function(error) {
            toastr.error(error.data.message, 'Problema!');
          });
          $scope.btnCancelar();
        };

        $scope.btnCancelar = () => {
          $scope.itemElement = null;
          init();
        }

        $scope.onSearchMaterial = () => {
          if($scope.searchMaterial){
            Restangular.all('search/campo').customGET('', {
              query: $scope.searchMaterial
            }).then((response) => {
              $scope.list = response.data;
              $scope.server = true;
            });
          }
        };
      }
    })
  }

  $scope.deleteItem = (item, $index) => {
    if(item){
      Restangular.all("examenes/material/delete").customPOST({
        id: item.id
      }, '').then((response) => {
        toastr.success(response.message, 'Exito!');
        $scope.data.materiales.splice($index, 1);
      });
    }
  }

  $scope.deleteCampoItem = (item, $index) => {
    if(item){
      Restangular.all("examenes/campo/delete").customPOST({
        id: item.id
      }, '').then((response) => {
        toastr.success(response.message, 'Exito!');
        $scope.campos.splice($index, 1);
      });
    }
  }
}
