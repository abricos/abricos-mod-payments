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

    NS.ButtonWidget = Y.Base.create('InfoWidget', SYS.AppWidget, [], {
        buildTData: function(){
            var form = this.get('form');
            if (!form){
                return {};
            }
            var tp = this.template,
                params = form.get('params'),
                method = form.get('method'),
                lstParams = "";

            for (var n in params){
                lstParams += tp.replace('param', {
                    name: n,
                    value: params[n]
                });
            }

            return {
                method: method === 'LINK' ? 'GET' : method,
                button: tp.replace('button' + method, {
                    url: form.get('url'),
                }),
                url: form.get('url'),
                params: lstParams
            };
        },
        onInitAppWidget: function(err, appInstance, options){
            var form = this.get('form');

            if (!form){
                return;
            }

            form.use('buttons', this._onLoadEngineComponent, this);
        },
        destructor: function(){
            if (this.infoWidget){
                this.infoWidget.destroy();
            }
            this.infoWidget = null;
        },
        _onLoadEngineComponent: function(err, NSEngine){
            var tp = this.template,
                srcInfo = this.get('srcInfo'),
                form = this.get('form');

            if (srcInfo){
                this.infoWidget = new NSEngine.InfoWidget({
                    srcNode: srcInfo,
                    form: form
                });
            }
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget,param,buttonPOST,buttonGET,buttonLINK'},
            srcInfo: {value: null},
            form: {value: null}
        },
        CLICKS: {},

    });

};
