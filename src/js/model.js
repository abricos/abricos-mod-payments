var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['appModel.js']}
    ]
};
Component.entryPoint = function(NS){
    var Y = Brick.YUI,
        SYS = Brick.mod.sys;

    NS.Request = Y.Base.create('request', SYS.AppModel, [], {
        structureName: 'Request'
    });

    NS.RequestList = Y.Base.create('requestList', SYS.AppModelList, [], {
        appItem: NS.Request
    });

    NS.Order = Y.Base.create('order', SYS.AppModel, [], {
        structureName: 'Order'
    });

    NS.Form = Y.Base.create('Form', SYS.AppModel, [], {
        structureName: 'Form',
        use: function(component, callback, context){
            var module = this.get('engineModule');
            Brick.use(module, component, callback, context);
        }
    });

    NS.Config = Y.Base.create('config', SYS.AppModel, [], {
        structureName: 'Config'
    });

    NS.Config = Y.Base.create('config', SYS.AppModel, [], {
        structureName: 'Config'
    });
};
