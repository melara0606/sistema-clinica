'use strict';

import $ from 'jquery'

module.exports = function uiJqInjectingFunction(uiJqConfig, $rootScope, JQ_CONFIG, uiLoad, $timeout) {
  return {
    restrict: 'A',
    compile: function uiJqCompilingFunction(tElm, tAttrs) {
      if (!angular.isFunction(tElm[tAttrs.uiJq]) && !JQ_CONFIG[tAttrs.uiJq]) {
          throw new Error('ui-jq: The "' + tAttrs.uiJq + '" function does not exist');
      }

      var options = uiJqConfig && uiJqConfig[tAttrs.uiJq];
      return function uiJqLinkingFunction(scope, elm, attrs) {

        if (attrs.ngModel && elm.is('select,input,textarea')) {
          elm.bind('change', function() { elm.trigger('input'); });
        }

        function callPlugin() {
          let options = Object.assign(scope.$eval('[' + attrs.uiOptions + ']')[0], uiJqConfig);
          $timeout(function() {
            if(attrs.uiJq === 'dataTable'){
              $(elm).DataTable(options);
            }
          }, 0, false);
        }

      function refresh(){
        if (attrs.uiRefresh) {
          scope.$watch(attrs.uiRefresh, function() { callPlugin(); });
        }
      }

      if ( JQ_CONFIG[attrs.uiJq] ) {
          uiLoad.load(JQ_CONFIG[attrs.uiJq]).then(function() {
              callPlugin();
              refresh();
          }).catch(function() {  });
      } else {
          callPlugin();
          refresh();
      }
    };
   }
 };
}
