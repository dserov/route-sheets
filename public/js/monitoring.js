let map = null;
let vehicleTree;
let swfobject;
const apiVideoHost = '77.221.215.195'; // ip and port required!
const videoServerHost = '77.221.215.195';
const videoServerPort = '6605';
let selectVideoWindowIndex = 0;
let playingStatusArray = [];
let vehiclesList = []; // тут будет инфа статуса по всем машинкам. Коорднаты, онлайновость, направление движения
let geoStorage = null;
const TREEIMAGEPATH = '/images/vehicle/';
const MAPIMAGEPATH = '/images/map/';
let isVideoInitFinished = false;
let isVisibleGeoZones = true;
let isLabelOnGeoZones = true;
let isUpdateVehiclesStatusAndPositionInProgress = false;

let makeVehiclesListItem = function (id) {
  return {
    id: id, // 210120
    title: '', // Agava_Test
    node: null, // ссылка на узел в дереве
    direction: 6, // угол поворота машинки по часовой стрелке 0-7 (0-360 разделен на секторы от 0-8)
    statusType: 1, // 3 - alarm, io, 1 - offline, 0 - online, parkaccon, stopaccof, stopaccon
    vehicleType: 1, // тип иконки машинки. 1 - лековая, 2 грузовая и т.п.
    vehicleMark: null, // если null - машинка по карте не ездит
    vehiclePoint: { // координаты машинки на карте
      lat: 0,
      lng: 0,
    },
    geoCircle: null, // список геообъектов, в текущий момент в поле радиуса машинки

    /** обновление иконки, только, если произошла смена статуса.
    *   response - status item from StandardApiAction_getDeviceStatus.action
    */
    setStatusType: async function (response) {
      // update direction
      let newDirection;
      if (response.hx != null) {
        newDirection = ((response.hx + 22) / 45) & 0x7;
      } else {
        newDirection = 0;
      }

      let needUpdateIcon = this.direction !== newDirection;

      // status type
      let newStatusType = (response.ol * 1 > 0 ? 0 : 1); // 0 - online, 1 = offline

      needUpdateIcon = needUpdateIcon || (this.statusType !== newStatusType);

      this.direction = newDirection;
      this.statusType = newStatusType;

      if (needUpdateIcon) {
        await this.updateMapIcon();
        await this.updateNodeIcon();
      }
    },

    updateNodeIcon: function (online) {
      if (!this.node) {
        return;
      }

      this.node.icon = TREEIMAGEPATH + this.getVehicleImage(2);
      this.node.renderTitle(); // update icon, title, etc...
    },

    updateMapIcon: async function () {
      if (!this.vehicleMark) {
        return;
      }

      this.vehicleMark.options.set('iconImageHref', MAPIMAGEPATH + this.getVehicleImage());
    },

    /**
     * Передавая значения координат, перемещаем машинку и "засвеченные" геозоны
     * геозоны по радиусу загрузим /monitoring/geoDistance?lat=54.987680&lng=73.450462&radius=1000
     *
     * @param lat
     * @param lng
     */
    updatePosition: async function (lat, lng) {
      lat *= 1;
      lng *= 1;
      if (lat === 0 || lng === 0) {
        console.log('lat is empty', lat);
        console.log('lng is empty', lng);
        return;
      }

      // проверим, отображать ли машинку на карте
      if (!this.node.selected) {
        this.removeVehicle();
        return;
      }

      if (this.vehicleMark === null) {
        await this.addVehicle(lat, lng);
        return;
      }

      // move to new position
      await this.moveVehicle(lat, lng);
    },
    moveVehicle: async function (lat, lng) {
      // сверим координаты. нет смысла двигать машинку, если она стоит на месте
      if (this.vehiclePoint.lat != lat || this.vehiclePoint.lng != lng) {
        console.log('move vehicle');
        // передвинем машинку и радиус
        try {
          if (this.vehicleMark) {
            await this.vehicleMark.geometry.setCoordinates([lat, lng]);
          }

          if (this.geoCircle) {
            // скроем объекты в радиусе
            await geoStorage.searchInside(this.geoCircle).setOptions('visible', false);

            // передвинем
            await this.geoCircle.geometry.setCoordinates([lat, lng]);
          }
        } catch (e) {
        }
      }

      try {
        // покажем объекты в радиусе
        geoStorage.searchInside(this.geoCircle).search('properties.geoType == "geoZone"').setOptions('visible', isVisibleGeoZones);
        geoStorage.searchInside(this.geoCircle).search('properties.geoType == "geoLabel"').setOptions('visible', isVisibleGeoZones && isLabelOnGeoZones);
      } catch (e) {
      }
    },
    addVehicle: async function (lat, lng) {
      try {
        // Создаём макет содержимого.
        let myIconContentLayout = ymaps.templateLayoutFactory.createClass(
          '<div style="color: #000000; font-weight: bold;">$[properties.iconContent]</div>'
        );

        let properties = {
          // iconCaption: this.title,
          iconContent: this.title,
          // hintContent: this.title,
          // baloonContent: this.title,
        };

        this.vehiclePoint.lat = lat;
        this.vehiclePoint.lng = lng;
        this.vehicleMark = new ymaps.Placemark([lat, lng], properties, {
          iconLayout: 'default#imageWithContent',
          iconImageHref: MAPIMAGEPATH + this.getVehicleImage(),
          iconImageSize: [32, 32],
          iconImageOffset: [-16, -16],
          // Смещение слоя с содержимым относительно слоя с картинкой.
          iconContentOffset: [0, 32],
          iconContentLayout: myIconContentLayout,
        });
        await map.geoObjects.add(this.vehicleMark);

        // circle for find objects
        this.geoCircle = new ymaps.Circle([[lat, lng], 500], properties, {
          fillOpacity: 0.2,
          strokeOpacity: 0.2
        });
        await map.geoObjects.add(this.geoCircle);

        // покажем только те объекты, что в выбранной области
        geoStorage.searchInside(this.geoCircle).search('properties.geoType == "geoZone"').setOptions('visible', isVisibleGeoZones);
        geoStorage.searchInside(this.geoCircle).search('properties.geoType == "geoLabel"').setOptions('visible', isVisibleGeoZones && isLabelOnGeoZones);
      } catch (e) {
      }
    },
    removeVehicle: async function () {
      // спрячем геозоны, удалим круг
      if (this.geoCircle) {
        try {
          await geoStorage.searchInside(this.geoCircle).setOptions('visible', false);
          await map.geoObjects.remove(this.geoCircle);
        } catch (e) {
        }
        this.geoCircle = null;
      }

      // удалим геометку машинки
      if (this.vehicleMark) {
        try {
          await map.geoObjects.remove(this.vehicleMark);
        } catch (e) {
        }
        this.vehicleMark = null;
      }
      this.vehiclePoint.lat = 0;
      this.vehiclePoint.lng = 0;
    },
    getStatusName: function (status) {
      if (0 == status) {
        return "/online/";
      } else if (1 == status) {
        return "/offline/";
      } else if (2 == status) {
        return "/parkaccon/";
      } else if (9 == status) {
        return "/stopaccon/";
      } else if (10 == status) {
        return "/stopaccoff/";
      } else if (11 == status) {
        return "/io/";
      } else {
        return "/alarm/";
      }
    },
    getVehicleImage: function (imgIndexForce) {
      let imgIndex = (imgIndexForce === undefined ? (Number(this.direction) & 0x7) : Number(imgIndexForce));
      let statusType = Number(this.statusType);
      let image = null;
      if (statusType < 4 || statusType == 9 || statusType == 10 || statusType == 11) {
        image = this.vehicleType + this.getStatusName(statusType) + (imgIndex + 1) + ".png";
      } else if (statusType == 99) {
        image = '10' + this.getStatusName(0) + (imgIndex + 1) + ".png";
      } else {
        if (4 == statusType) {
          image = "parking.gif";
        } else if (5 == statusType) {
          image = "qidian.gif";
        } else if (6 == statusType) {
          image = "zhongdian.gif";
        } else if (7 == statusType) {
          image = "alarm.gif";
        } else if (8 == statusType) {
          image = "alarmmarker.gif";
        }
      }

      return image;
    },
    setVehicleStatus: function (response) {
      if (Number(response.ol) > 0) {
        data.image = 0; // online
        if (this.isLocationInvalid()) {
          //是否停车
          // 重载和空载
          // 空车和重车
          // 判断车辆地图设备是否渣土车或出租车
          /*if (this.isWeightVehi()) {
              // 待处理！李德超
              data.image = 9;
          } else*/ if (this.isParkedNew()) {
            data.image = 10;	//停车
          }  else {//判断是否为静止，并且ACC开启  怠速
            if (this.isIdling()) {
              data.image = 9;	//停车未熄火
            }
          }
          // 定位有效才算报警状态
          if (this.isAlarm()) {
            data.image = 3;	//报警状态
          }
          // 终端服务到期
          if ((!this.isServicePeriod()) || (this.isServicePeriod() && !this.isServicePeriodEx())) {
            data.image = 3;	//报警状态
          }
        } else {//定位无效
          data.image = 2;	//无效
        }
      } else {
        data.image = 1;	//离线
      }
      //公交线路
      return data;
    },
  };
};

ymaps.ready(initMap);

window.addEventListener('load', function () {
  reloadTree();
  initVideoTab();

  // запустим сервис обновления статусов всех машинок
//  setTimeout(updateVehiclesStatusAndPosition, 5000);
});

function initMap() {
  // [широта, долгота] [latitude, longitude]
  map = new ymaps.Map('map', {
    center: [55.0378599938, 73.4201805827],
    zoom: 15
  });

  // Создадим переключатель видимости геозон
  addPropertiesListControlToMap();

  // add all geoObjects to map
  addAllGeoObjectsToMap();

  map.geoObjects.events.add('click', function (event) {
    let target, geoZoneId, balloonContent;
    try {
      target = event.get('target');
      geoZoneId = target.properties.get('geoPointId');
      balloonContent = target.properties.get('balloonContent');
    } catch (e) {
      return;
    }

    // отобразим балун, если он есть. Если его нет - запросим с сервера
    if (balloonContent) {
      // покажем балун
      target.balloon.open();
      console.log('show exists baloon');
    } else {
      if (geoZoneId === undefined) {
        return;
      }
      console.log('loading baloon');
      target.properties.set('balloonContent', "Идет загрузка данных...");
      target.balloon.open();
      $.getJSON('/monitoring/balloon/' + geoZoneId, function (response) {
        let balloonContent = '';
        if (typeof response === 'object' && response.length > 0) {
          // КОНТРАГЕНТ-КОЛИЧЕСТВО БАКОВ- УТ НОМЕР
          response.forEach(function (item) {
            balloonContent += `<p><span>Количество баков: </span>${item.export_volume}<br><span>Тип бака: </span>${item.container_type}<br><span>УТ номер: </span>${item.ut_number}<br></p>`;
          });
        } else {
          balloonContent = 'Данных не найдено...';
        }
        console.log('response', response);
        target.properties.set('balloonContent', balloonContent);
      });
    }
  })
}

function addAllGeoObjectsToMap() {
  let geoZones = [];
  let myIconContentLayout = ymaps.templateLayoutFactory.createClass(
    '<div style="color: #FFFFFF; font-weight: bold;">{{properties.iconCaption}}</div>'
  );
  geo_list.forEach(function (item) {
    geoZones.push(
      new ymaps.GeoObject(
        {
          geometry: item.geometry,
          properties: {
            iconCaption: item.description,
            iconContent: item.description,
            hintContent: item.name,
            geoPointId: item.id,
            geoType: 'geoZone',
          },
          options: {
            // iconLayout: 'default#imageWithContent',
            // iconContentLayout: myIconContentLayout,
            // iconContentOffset: [-15, 0],
          }
        }
      )
    );

    let geometry = item.geometry;
    geometry.type = "Point";
    geometry.radius = undefined;
    geoZones.push(
      new ymaps.GeoObject(
        {
          geometry: geometry,
          properties: {
            iconCaption: item.name,
            geoPointId: item.id,
            geoType: 'geoLabel',
          },
          options: {
            preset: 'islands#darkBlueStretchyIcon',
          }
        }
      )
    );
  });
  geoStorage = ymaps.geoQuery(geoZones).setOptions('visible', false).addToMap(map);
}

async function addPropertiesListControlToMap() {
  let geoPropertiesList = new ymaps.control.ListBox({
    data: {
      content: 'Геозоны'
    },
    items: [
      new ymaps.control.ListBoxItem({data: {content: 'Показаны'}, state: {selected: true}}),
      new ymaps.control.ListBoxItem({data: {content: 'Скрыты'}}),
      new ymaps.control.ListBoxItem({data: {content: 'Подписаны'}}),
      new ymaps.control.ListBoxItem({data: {content: 'Не подписаны'}}),
    ]
  });
  geoPropertiesList.get(0).events.add('click', function () {
    geoPropertiesList.get(0).deselect();
    geoPropertiesList.get(1).deselect();
    geoPropertiesList.get(2).enable();
    geoPropertiesList.get(3).enable();

    // Геозоны включены
    isVisibleGeoZones = true;
    updateGeoZones();

    // Закрываем список.
    geoPropertiesList.collapse();
  });
  geoPropertiesList.get(1).events.add('click', function () {
    geoPropertiesList.get(0).deselect(); // state.set('selected', false);
    geoPropertiesList.get(1).deselect(); // state.set('selected', false);
    // geoPropertiesList.get(0).state.set('selected', false);
    // geoPropertiesList.get(1).state.set('selected', false);

    geoPropertiesList.get(2).disable();
    geoPropertiesList.get(3).disable();

    // Геозоны отключены
    isVisibleGeoZones = false;
    updateGeoZones();

    // Закрываем список.
    geoPropertiesList.collapse();
  });
  geoPropertiesList.get(2).events.add('click', function () {
    geoPropertiesList.get(2).deselect();
    geoPropertiesList.get(3).deselect();

    // Геозоны подписаны
    isLabelOnGeoZones = true;
    updateGeoZones();

    // Закрываем список.
    geoPropertiesList.collapse();
  });
  geoPropertiesList.get(3).events.add('click', function () {
    geoPropertiesList.get(2).deselect();
    geoPropertiesList.get(3).deselect();

    // Геозоны не подписаны
    isLabelOnGeoZones = false;
    updateGeoZones();

    // Закрываем список.
    geoPropertiesList.collapse();
  });
  await map.controls.add(geoPropertiesList, {floatIndex: 0});
}

var object;

async function addObject() {
  object = makeVehiclesListItem('1', 'TEST');
  await object.updatePosition('54.987680', '73.450462');
  map.setBounds(object.geoCircle.geometry.getBounds());
}

function showErrorMessage(message) {
  let vehicleTreeDiv = document.getElementById('vehicle_tree');
  if (!vehicleTreeDiv) {
    alert('Error: ' + message);
    return;
  }

  vehicleTreeDiv.insertAdjacentHTML('afterend', `<div class="alert alert-danger" role="alert">Ошибка: ${message}</div>`);
//  setTimeout(() => { document.querySelector('.alert.alert-danger').remove() }, 10000);
}

async function updateGeoZones() {
  if (isVisibleGeoZones) {
    vehiclesList.forEach(async vehicle => {
      if (vehicle.geoCircle) {
        try {
          // покажем объекты в радиусе
          await geoStorage.searchInside(vehicle.geoCircle).search('properties.geoType == "geoZone"').setOptions('visible', isVisibleGeoZones);
          await geoStorage.searchInside(vehicle.geoCircle).search('properties.geoType == "geoLabel"').setOptions('visible', isVisibleGeoZones && isLabelOnGeoZones);
        } catch (e) {}
      }
    });
  } else {
    // скроем все геозоны
    await geoStorage.setOptions('visible', false);
  }
}

/**
 * FancyTreeNode node
 *
 * @param node
 */
function moveMapToLastVehicle(node) {
  if (node.isSelected() && node.data.node_type === 'vehicle') {
    moveToNode(node.data.id);
    return;
  }

  // ищем последний в списке узел с типом vehicle
  let rootNode = node.isSelected() ? node : vehicleTree;
  let currentSelectedVehicles = rootNode.getSelectedNodes().filter(function (node) {
    return node.data.node_type === 'vehicle';
  }).map(function (node) {
    return node.data.id;
  });
  if (currentSelectedVehicles.length === 0) {
    return;
  }
  let nodeId = currentSelectedVehicles.pop();

  // двигаем к нему
  moveToNode(nodeId);
}

function moveToNode(id) {
  let intervalHandle = setInterval(function () {
    let idx = vehiclesList.findIndex(item => item.id === id);
    if (idx === -1) {
      return;
    }

    let vehicle = vehiclesList[idx];
    if (vehicle.geoCircle === null) {
      return;
    }

    // есть на карте!
    map.setBounds(vehicle.geoCircle.geometry.getBounds());
    clearInterval(intervalHandle);
  }, 500);

  // три секунды будем ждать координаты
  setTimeout(() => clearInterval(intervalHandle), 3000);
}

async function reloadTree() {
  $('#vehicle_tree').fancytree({
    checkbox: true,
    selectMode: 3,
    clickFolderMode: 2,
    icon: true,
    source: [],
    activate: function (event, data) {
      // console.dirxml(data);
      // console.log(event.type + ": " + data.node);
    },
    select: async function (event, data) {
      // console.log(
      //   event.type + ": " + data.node.isSelected() + " " + data.node
      // );

      // отфильтруем машинки
//        await updateVehiclesOnMap();

      // переместим карту
//        moveMapToLastVehicle(data.node);
    },
    dblclick: function (event, data) {
      playVideoFromThisNode(data.node);
    }
  });

  vehicleTree = $.ui.fancytree.getTree('#vehicle_tree');

  // запрос данных по всем машинкам
  try {
    let data = await getDataFromVideoServer(`/StandardApiAction_queryUserVehicle.action`, {
      jsession: '00000000000000000000000000000000',
      language: 'en'
    });

    // добавим машинки
    parseVehicleJsonToTreeNodes(data);

    // развернем только группы
    let rootNode;
    if (rootNode = findNodeById(0)) {
      rootNode.setExpanded();
    }
  } catch (e) {
    showErrorMessage(e.message);
  }
}

async function updateVehiclesStatusAndPosition() {
  if (isUpdateVehiclesStatusAndPositionInProgress) {
    return;
  }
  isUpdateVehiclesStatusAndPositionInProgress = true;

  let idList = vehiclesList.map(item => item.id);
  if (idList.length === 0) {
    isUpdateVehiclesStatusAndPositionInProgress = false;
    return;
  }

  let data = await getDataFromVideoServer('/StandardApiAction_getDeviceStatus.action', {
    jsession: Cookies.get('jsession'),
    devIdno: idList.join(','),
    toMap: 1,
    language: 'en',
  });
  data.status.forEach(async function (response) {
    let objIndex = vehiclesList.findIndex(obj => obj.id === response.id);
    if (objIndex === -1) {
      return;
    }

    // нужно ли сменить иконки на карте и в дереве машинок
    vehiclesList[objIndex].setStatusType(response);

    await vehiclesList[objIndex].updatePosition(response.mlat, response.mlng);
  });

  isUpdateVehiclesStatusAndPositionInProgress = false;
}

function playVideoFromThisNode(node) {
  if (!node || node.isFolder() || !node.data || node.data.node_type !== 'vehicle' || !node.data.id || !swfobject) {
    return;
  }

  let devId = node.data.id;
  let deviceName = node.title;

  // остановим воспроизведение, если уже идет
  if (playingStatusArray[0]) {
    swfobject.stopVideo(0);
  }

  if (playingStatusArray[1]) {
    swfobject.stopVideo(1);
  }

  // воспроизведение 0-го и 1-го каналов в окнах
  swfobject.setVideoInfo(0, `${deviceName} - CH1`);
  swfobject.startVideo(0, Cookies.get('jsession'), devId, 0, 0, true);

  swfobject.setVideoInfo(1, `${deviceName} - CH2`);
  swfobject.startVideo(1, Cookies.get('jsession'), devId, 1, 0, true);
}

function findNodeById(id) {
  return vehicleTree.findFirst(function (node) {
    return node.data.id == id;
  })
}

function parseVehicleJsonToTreeNodes(data) {
  if (data.result !== 0) {
    showErrorMessage('parseVehicleJsonToTreeNodes error: ' + data.result);
    return;
  }

  // add root node
  let rootNode = vehicleTree.getRootNode().addNode({
    title: 'Monitoring center',
    id: 0,
    folder: true,
    icon: '/images/vehicle/all_group.png'
  });

  // add companies nodes
  data.companys.forEach(function (company) {
    if (company.pId === 0) {
      rootNode.addNode({
        id: company.id,
        title: company.nm,
        folder: true,
        node_type: 'company',
        icon: '/images/vehicle/group.png',
      });
    }
  });

  data.vehicles.forEach(function (vehicle) {
    let companyNode = findNodeById(vehicle.pid);
    if (companyNode === null) {
      // company node not found
      return;
    }

    if (vehicle.dl.length === 0) {
      return; // no video device installed
    }

    // add vehicle node
    let vehicleObj = {
      id: vehicle.dl[0].id,
      title: vehicle.nm,
      checkbox: true,
      node_type: 'vehicle',
      vehicleIconType: vehicle.ic,
      icon: TREEIMAGEPATH + vehicle.ic + '/offline/3.png',
    };

    // add vehicle item
    let item = makeVehiclesListItem(vehicleObj.id);
    item.vehicleType = vehicle.ic;
    item.title = vehicleObj.title;
    vehicleObj.icon = TREEIMAGEPATH + item.getVehicleImage(2, 1);
    item.node = companyNode.addNode(vehicleObj);
    vehiclesList.push(item);
  });
}

async function videoServerLogin() {
  let login = 'admin';
  let password = 'admin';
  let url = '/StandardApiAction_login.action';
  let params = {
    account: login,
    password: password
  };

  return await getRawData(url, params);
}

function initVideoTab() {
  if (isVideoInitFinished) {
    return;
  }

  for (let i = 0; i < 2; i++) {
    playingStatusArray.push(false);
  }

  let width;
  try {
    let target = document.getElementById('cmsv6flash');
    do {
      width = Math.round(target.getBoundingClientRect().width);
      if (width > 0) {
        break;
      }
    } while ((target = target.parentNode) !== null);
  } catch (e) {
  }
  if (width === undefined) {
    return;
  }

  swfobject = new Cmsv6Player({
    domId: "cmsv6flash",
    allowFullscreen: "true",
    allowScriptAccess: "always",
    bgcolor: "#FFFFFF",
    wmode: "transparent",
    isVodMode: false, // live video
    width: width,
    height: (width / 4 * 3), // должно два окна в ряд поместиться
    lang: 'en'
  });

  initVideoFlash();
}

// Execute after plugin initialization is complete
function initVideoFlash() {
  if (typeof swfobject === "undefined" ||
    typeof swfobject.setWindowNum === "undefined") {
    setTimeout(initVideoFlash, 50);
  } else {
    // Initialize plugin language
    // swfobject.setLanguage(language);

    // First create all windows
    swfobject.setWindowNum(4);

    // Set server information
    swfobject.setServerInfo(videoServerHost, videoServerPort);
    isVideoInitFinished = true;

    // fix window count
    let parent = document.getElementById('cmsv6flash');
    document.getElementById('Cmsv6H5Video-cmsv6flash-0').style.height = (parent.getBoundingClientRect().height) / 2 + 'px';
    document.getElementById('Cmsv6H5Video-cmsv6flash-1').style.height = (parent.getBoundingClientRect().height) / 2 + 'px';
    document.getElementById('Cmsv6H5Video-cmsv6flash-0').ondblclick = null;
    document.getElementById('Cmsv6H5Video-cmsv6flash-1').ondblclick = null;
    document.getElementById('Cmsv6H5Video-cmsv6flash-2').remove();
    document.getElementById('Cmsv6H5Video-cmsv6flash-3').remove();
    parent.querySelectorAll('div[name="menuUI"]').forEach(el => el.remove());
    parent.style.height = (parent.getBoundingClientRect().height) / 2 + 'px';
  }
}

// this default event callback for swfobject object
function onTtxVideoMsg(index, type) {
  if (index != null && index != "") {
    index = parseInt(index, 10);
  }
  //窗口事件
  //window message
  if (type == "select") {
    //选中窗口     		select window
    selectVideoWindowIndex = index;
    $('#eventTip').html('Select event: selected  Window ' + (index + 1) + ' ');
  } else if (type == "full") {
    //全屏			Full screen
  } else if (type == "norm") {
    //退出全屏			Exit full screen
  }
  //视频播放事件
  //video play messsage
  else if (type == "stop") {
    //停止播放			stop playing
    playingStatusArray[index] = false;
  } else if (type == "start") {
    //开始播放			Start play
    playingStatusArray[index] = true;
  } else if (type == "sound") {
    //开启声音			Turn on the sound
  } else if (type == "silent") {
    //静音			Mute
  } else if (type == "play") {
    //暂停或停止后重新播放			Play again after pause or stop
  } else if (type == "PicSave") {
    //截图			screenshot
  }
  //对讲事件
  //Intercom messsage
  else if (type == "startRecive" || type == "uploadRecive" || type == "loadRecive") {
    //开启对讲			Open intercom
  } else if (type == "stopTalk") {
    //关闭对讲			Turn off intercom
  } else if (type == "playRecive") {
    //对讲中			Talkback
  } else if (type == "reciveStreamStop" || type == "reciveNetError" || type == "reciveStreamNotFound") {
    //对讲异常(网络异常等)			Talkback anomalies (network exceptions, etc.)

  } else if (type == "uploadNetClosed" || type == "uploadNetError") {
    //连接异常 			Connection exception
  } else if (type == "upload") {
    //对讲讲话			Talkback speech
  } else if (type == "uploadfull") {
    //对讲讲话结束		Talkback speech ends
  }
  //监听事件
  //Listen messsage
  else if (type == "startListen") {
    //开始监听      		Start listening
  } else if (type == "stopListen") {
    //主动停止监听 		Active stop monitoring

  } else if (type == "listenNetError") {
    //网络异常  			Network anomaly

  } else if (type == "playListen") {
    //监听中	  		In listening
  } else if (type == "loadListen" || type == "listenStreamNotFound" || type == "listenStreamStop") {
    //等待请求监听	   	Waiting request monitoring
  } else if (type == 'showDownLoadDialog') {
    alert("down pcm tool");
    downPcmTool();
  } else if (type == 'isTalking') {
    alert("is talking");
  }
}

// Функция получает инфу по урлу и данным, если неудачно, попробует перелогиниться и получить снова
async function getDataFromVideoServer(url, params) {
  // пробуем получить данные
  let i = 2;

  while (i > 0) {
    params.jsession = Cookies.get('jsession');
    try {
      return await getRawData(url, params);
    } catch (e) {
      console.log('catch', e.message);
      i--;
      try {
        let jsession = (await videoServerLogin()).jsession;

        Cookies.set('jsession', jsession, {
          expires: 3600,
          domain: '',
          path: '/'
        });

      } catch (e) {
        console.log('catch2', e.message);
        showErrorMessage(e.message);
        break;
      }
    }
  }

  throw new Error('Не удалось получить данные');
}

async function getRawData(url, params) {
  return new Promise((resolve, reject) => {
    let xhr = $.getJSON(`https://${apiVideoHost}${url}`, params, function (data) {
      if (data.result === 0) {
        // успех
        resolve(data);
        return;
      }

      reject(new Error(data.message));
    });
    xhr.fail(function (a, b, c) {
      console.log(a);
      console.log(b);
      console.log(c);
      reject(new Error('Не удалось получить информацию с видео-сервера'));
    });
  });
}
