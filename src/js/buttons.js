var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;



    NS.InfoWidget = Y.Base.create('InfoWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance, options){
        },
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'info'},
            form: {value: null}
        },
        CLICKS: {}
    });

    NS.ButtonWidget = Y.Base.create('InfoWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance, options){
            var tp = this.template,
                form = this.get('form');

            if (!form){
                return;
            }

            form.use();

            var engineModule = form.get('engineModule');


            console.log(form.getAttrs());

        },
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'button'},
            form: {value: null}
        },
        CLICKS: {}
    });

};