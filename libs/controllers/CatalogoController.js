import { filter, findIndex, forEach, groupBy, remove } from 'lodash'

module.exports.controllers = function($scope, $rootScope, Restangular, catalogo) {
    $scope.catalogo = catalogo;
}

module.exports.item = function($scope, $rootScope, Restangular, object, toastr, objectCategoria, $uibModal) {
    $scope.item = {};
    $scope.isEditing = false;
    $scope.objectItem = [];
    $scope.objectGroup = [];
    $scope.object = object.data;
    $scope.objectGroupIndex = []

    $scope.isGroup = false;
    $scope.isGrupoCategoria = false;

    $scope.onCreateGrupo = () => $scope.isGroup = true;
    $scope.onCreateCategoriaGrupo = () => $scope.isGrupoCategoria = true;

    $scope.onCerrarCreateGroup = () => {
      $scope.isGroup = false;
      $scope.queryGrupoModel = null
      $scope.objectGroupIndex = []
    }

    $scope.grupos = object.data.grupos;

    if($scope.object.id == 1){
      $scope.objectGroup = groupBy($scope.object.gruposObject, 'grupo_seleccion');
    }

    // creacion de nuevos grupos de seleccion
    $scope.addGrupoSeleccion = () => {
        var modal = $uibModal.open({
            templateUrl: 'public/templates/modals/crear.grupo.seleccion.html',
            controller: ($scope, $uibModalInstance, toastr) => {
                $scope.is_multiple = false
                $scope.close = () => $uibModalInstance.close({ response: false })
                $scope.addGrup = () => {
                    Restangular.all('campos/creacion/add').customPOST({
                        nombre: $scope.name,
                        isMultiple: $scope.is_multiple
                    }, '').then( json => {
                        toastr.success('Hemos creado con exito el nuevo grupo.', 'Exito')
                        $uibModalInstance.close({ response: true, data: json.data })
                    });                    
                }
            }
        })
        
        modal.result.then(json => {
            if(json.response){
                $scope.object.grupos.push({
                    "grupo_seleccion" : json.data.grupo_seleccion,
                    "nombre_grupo"    : json.data.nombre_grupo
                });
            }
        })
    }

    $scope.editGrup = (queryGrupoModel) => {
        var modalEdit = $uibModal.open({
            resolve: {
                "json": ["Restangular", function(Restangular){
                    return Restangular.all('seleccion_campos/data').customGET(queryGrupoModel);
                }]
            },
            templateUrl: 'public/templates/modals/edit.grupo.seleccion.html',
            controller: ($scope, json, $uibModalInstance, toastr) => {
                let { nombre_grupo, is_multiple } = json.data[0]
                $scope.name = nombre_grupo
                $scope.is_multiple = !!is_multiple

                /* Funciones */
                $scope.close = () => $uibModalInstance.close({ response: false })
                $scope.editGrup = () => {
                    Restangular.all('campos/creacion/edit').customPOST({
                        nombre: $scope.name,
                        isMultiple: $scope.is_multiple,
                        grupSelecion: json.data[0].grupo_seleccion
                    }, '').then( r => {
                        toastr.success('Hemos actualizado con exito al grupo.', 'Exito')
                        $uibModalInstance.close({ response: true, data: r.data[0] })
                    });
                }
            }
        })   
        
        modalEdit.result.then(d => {
            if(d.response){
                var $index = findIndex($scope.object.grupos, {
                    'grupo_seleccion': queryGrupoModel
                })
                $scope.object.grupos[$index] = {
                    "grupo_seleccion"   : d.data.grupo_seleccion,
                    "nombre_grupo"      : d.data.nombre_grupo
                }
            }
        })
    }

    $scope.editGrupo = (element) => {
        element.editing = true
        element.nuevoNombre = element.nombre_seleccion
    }

    $scope.doneElement = (element) => {
      Restangular.all('campos/creacion/update').customPOST({
          data: element
      }).then( json => {
        let { data } = json
        let $findIndex = findIndex($scope.objectGroupIndex, {
            id: element.id
        })
        
        $scope.objectGroupIndex[$findIndex] = {
          grupo_seleccion: data.grupo_seleccion,
          id: data.id,
          nombre_seleccion: data.nombre_seleccion,
          editing: false,
          nuevoNombre: data.nombre_seleccion
        };
      })
    }

    $scope.btnDeleteElement = (element) => {
        element.editing = false
    }

    $scope.changeSelection = () => {
        Restangular.all("campos/options/" + $scope.item.grupo_seleccion).customGET()
            .then((response) => {
                $scope.objectItem = response.data;
            })
    }

    $scope.addItemGroup = () => {
        let nameGroup = $scope.nameGroup.trim()
        if(nameGroup){
            Restangular.all('catalogoCampos/add/campus').customPOST({
                name:  nameGroup,
                grupoSeleccion: $scope.queryGrupoModel
            }, '').then((objectJson) => {
                let { message, data } = objectJson
                toastr.success(message, 'Exito!');
                $scope.objectGroupIndex.push(data)
                $scope.object.gruposObject.push(data)
                $scope.nameGroup = '';

                $scope.objectGroup = groupBy($scope.object.gruposObject, 'grupo_seleccion');
            }, (error) =>{
                toastr.error(error.data.message, 'Error!')
            })
        }
    }

    $scope.onChangeGrupoModel = () => {
        if($scope.queryGrupoModel){
            Restangular.all("campos/options/" + $scope.queryGrupoModel).customGET()
                .then((response) => {
                    $scope.objectGroupIndex = response.data
                })
        }else{
            $scope.objectGroupIndex = []
        }
    }

    $scope.deleteSelectionGroup = (object) => {
        Restangular.all(`campos/options/`).customPOST({code : object.id}, 'delete').then((json) => {
            let objectIndex = findIndex($scope.object.gruposObject, {  id: object.id });
            $scope.object.gruposObject.splice(objectIndex, 1);
            
            let objectCurrent = findIndex($scope.objectGroupIndex, { 'id' : object.id })
            $scope.objectGroupIndex.splice(objectCurrent, 1);

            $scope.objectGroup = groupBy($scope.object.gruposObject, 'grupo_seleccion');
        });
    }

    $scope.onValidateForm = ($valid) => {
        if($valid){
            let route = ($scope.item.id) ? 'catalogoCampos/' + $scope.item.id + '/edit' : 'catalogoCampos/add';
            Restangular.all(route).customPOST({
                item: $scope.item,
                object: $scope.object.id
            }).then((json) => {
                toastr.success(json.message, "Exito!");
                $scope.isEditing = false;

                if($scope.item.id){
                    $scope.object.campos[$scope.$index] = json.data;
                }else{
                    $scope.object.campos.unshift(json.data)
                }
            })
        }
    }

    $scope.onCreate = () => {
        $scope.isEditing = true;
        $scope.item = {};
        $scope.objectItem = [];
    }

    $scope.onCancelar =  function() {
        $scope.isEditing = false;
    }

    $scope.doneEdinting = function(item, $index) {
        $scope.item = item;
        if($scope.item.id_tipo_catalogo == 1){
            $scope.item.grupo_seleccion = $scope.item.grupo_seleccion.toString();
            $scope.changeSelection();
        }

        item.valor_opcional = item.valor_opcional ? true: false;
        $scope.$index = $index;
        $scope.isEditing = true;
    }

    // Configuracion de los campos multiples
    $scope.objectCategoria = objectCategoria.data;

    $scope.addCategoriaGrupo = () => {
        Restangular.all('objectCategoria').customPOST({
            nombre: $scope.nameCategoriaGroup.trim()
        }).then(json => {
            $scope.nameCategoriaGroup = ''
            toastr.success(json.message, 'Exito!')
            $scope.objectCategoria.push(json.data)
        })
    }

    $scope.deleteCategoriaGroup = (o, $index) => {
        Restangular.all('objectCategoria').customPOST({
            id: o.id
        }, 'deleteCategoriaGrupo').then(json => {
            toastr.success(json.message, 'Exito!')
            $scope.objectCategoria.splice($index, 1);
        }, (error) => {
            toastr.error(error.data.message, 'Error!')
        })
    }

    $scope.editCategoriaGrupo = (element) => {
        element.editing = true
        element.nuevoCategoriaNombre = element.nombre_categoria_grupo
    }

    $scope.doneElementC = (object, $index) => {
        Restangular.all('objectCategoria').customPOST({
            data: object
        }, 'update').then( json => {
          let { data, message } = json
         
          $scope.objectCategoria[$index] = {
            id: data.id,
            editing: false,
            nuevoNombre: data.nombre_categoria_grupo,
            nombre_categoria_grupo: data.nombre_categoria_grupo
          };
          toastr.success(message, 'Exito!')
        }, (error) => {
            toastr.error(error.data.message, 'Error!')
        })
    }

    $scope.ViewCategoriaGroup = (object) => {
        let modal = $uibModal.open({
            size: 'lg',
            templateUrl: 'public/templates/modals/view.categoria.campos.html',
            resolve: {
              grupos: ['Restangular', function(Restangular) {
                  return Restangular.all('objectCategoria').customGET(`grupos/${object.id}`)
              }]  
            },
            controller: ($scope, $uibModalInstance, grupos, toastr) => {
                $scope.grupos = grupos.data;
                $scope.gruposCategoria = grupos.grupos;

                $scope.sortableOptions = {
                    connectWith: '.connectedItemsExample .list'
                };

                $scope.cerrar = () => $uibModalInstance.close({})

                $scope.guardar = ($event) => {
                    Restangular.all('objectCategoria').customPOST({
                        id: object.id, 
                        data: $scope.gruposCategoria
                    }, 'grupos').then(json => {
                        toastr.success(json.message, 'Exito!')
                        $scope.cerrar();
                    })
                }
            }
        })
    }

    $scope.onCerrarCreateGroup = () => {
        $scope.isGrupoCategoria = false;
    }
}
