define(function () {
  'use strict';

  var mixin = {
    isBaseGrandTotalDisplayNeeded: function () {
      // you custom logic for displaying base grand total
      // returning false prevents the section from being displayed
      return false;
    }
  };

  return function (target) {
    return target.extend(mixin);
  };
});