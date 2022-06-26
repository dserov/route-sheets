var ttxVideoAll = {
    init: function (domId, width, height, params, playerType) {
        var path = ttxVideoAll.getPath();
        ttxVideoAll.playerPath = path + 'player.swf';
        ttxVideoAll.domId = domId;
        ttxVideoAll.width = width;
        ttxVideoAll.height = height;
        ttxVideoAll.version = '11.0.0';
        ttxVideoAll.params = params;

        if (playerType === 'flash' || playerType === 'h5') {
            ttxVideoAll.playerType = playerType;
        }
        if (ttxVideoAll.isBrowseIE()) {
            ttxVideoAll.playerType = 'flash';
        }
        if (!ttxVideoAll.playerType && localStorage.playerType && localStorage.playerType === 'flash') {
            ttxVideoAll.playerType = 'flash';
        }
        if (ttxVideoAll.playerType === 'flash') {
            ttxVideoAll.loadJs(path + "swfobject.js");
        } else {
            ttxVideoAll.loadJs(path + "js/cmsv6player.min.js");
        }
        ttxVideoAll.initTimer();
    },
    initTimer: function () {
        if (ttxVideoAll.playerType == 'flash') {
            if (typeof swfobject == "undefined") {
                setTimeout(ttxVideoAll.initTimer, 50);
            } else {
                swfobject.embedSWF(ttxVideoAll.playerPath, ttxVideoAll.domId, ttxVideoAll.width, ttxVideoAll.height, "11.0.0", null, null, ttxVideoAll.params, null);
            }
        } else {
            if(typeof Cmsv6Player == "undefined"){
                setTimeout(ttxVideoAll.initTimer, 50);
            } else {
                var options = {
                    domId: ttxVideoAll.domId,
                    width: ttxVideoAll.width,
                    height: ttxVideoAll.height,
                    isTtx: true,
                    showScreenshot: ttxVideoAll.params.showScreenshot,
					isVodMode: ttxVideoAll.params.isVodMode ? ttxVideoAll.params.isVodMode : false
                };
                if (ttxVideoAll.params.lang) {
                    options.lang = ttxVideoAll.params.lang
                }
                swfobject = new Cmsv6Player(options);
            }
        }
    },
    getPath: function () {
        var scripts = document.getElementsByTagName('script');
        for (let i = 0; i < scripts.length; i++) {
            var src = scripts[i].src;
            if (src.indexOf('swfobject-all.js') > -1) {
                return src.replace(/[^\/]+$/, "");
            }
        }
        return '';
    },
    switchType: function (playerType) {
        if (ttxVideoAll.isBrowseIE() && playerType != 'flash') {
			if (ttxVideoAll.params.lang == "zh") {
				alert('ie浏览器只支持flash播放');
			} else {
				alert('IE browser only supports flash');
			}
            return;
        }
        if (playerType !== 'flash' && playerType !== 'h5' && playerType !== 'auto') {
            return;
        }
        localStorage.playerType = playerType
        location.reload()
    },
    isBrowseIE: function () {
        return (ttxVideoAll.getBrowseVersion().indexOf("msie") != -1) ? true : false;
    },
    getBrowseVersion: function () {
        var userAgent = navigator.userAgent.toLowerCase();
        var browser = userAgent.match(/(firefox|chrome|safari|opera|msie)/);
        var browserId = "msie";
        var browserVersion = "";
        var isIE11 = (userAgent.toLowerCase().indexOf("trident") > -1 && userAgent.indexOf("rv") > -1);

        var ua = navigator.userAgent;
        var isAndroid = ua.indexOf('Android') > -1 || ua.indexOf('Adr') > -1; //android
        var isiOS = !!ua.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios

        if(isAndroid){
            return 'Android';
        }
        if(isiOS){
            return 'ios';
        }

        if (isIE11) {
            browserId = "msie";
            browserVersion = "11.0";
        } else {
            if (browser.length >= 2) {
                browserId = browser[1];
            } else {
                browserId = "msie";
            }
            browserVersion = (userAgent.match(new RegExp('.+(?:version)[\/: ]([\\d.]+)')) || userAgent.match(new RegExp('(?:' + browserId + ')[\/: ]([\\d.]+)')) || [0, '0'])[1];
        }
        return browserId + browserVersion;
    },
    loadCss: function (path) {
        if (!path || path.length === 0) {
            throw new Error('argument "path" is required !');
        }
        var head = document.getElementsByTagName('head')[0];
        var link = document.createElement('link');
        link.href = path;
        link.rel = 'stylesheet';
        link.type = 'text/css';
        head.appendChild(link);
    },
    loadJs: function (path) {
        if (!path || path.length === 0) {
            throw new Error('argument "path" is required !');
        }
        var head = document.getElementsByTagName('head')[0];
        var script = document.createElement('script');
        script.src = path;
        script.type = 'text/javascript';
        head.appendChild(script);
    }
}