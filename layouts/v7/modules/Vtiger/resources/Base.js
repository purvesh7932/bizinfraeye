Vtiger.Class('Vtiger_Base_Js', {},{

    _components : {},

    addComponents : function() {},

    init : function() {
        this.addComponents();
    },

    intializeComponents : function() {
        for(var componentName in this._components) {
            var componentInstance = this._components[componentName];
            componentInstance.registerEvents();
        }
    },
});