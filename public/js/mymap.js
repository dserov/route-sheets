var polygonOptions = {
    strokeColor: '#0000ff',
    fillColor: '#8080ff',
    interactivityModel: 'default#transparent',
    strokeWidth: 4,
    opacity: 0.7
};

var canvasOptions = {
    strokeStyle: '#0000ff',
    lineWidth: 4,
    opacity: 0.7
};

var map = null;
var isMapFullScreen = false;

ymaps.ready(init);

function init() {
    map = new ymaps.Map('map', {
        center: [55.0378599938, 73.4201805827],
        zoom: 15
    });
    let polygon = null;
    let objectsInsidePolygon = null;

    // add all geoObjects to map
    let allObjects = [];
    geo_list.forEach(function (item) {
        allObjects.push(
            new ymaps.GeoObject(
                {
                    geometry: item.geometry,
                    properties: {
                        hintContent: item.name,
                        baloonContent: item.description,
                        geoPointId: item.id
                    }
                }
            )
        );
    });

    const storage = ymaps.geoQuery(allObjects).setOptions('visible', false).addToMap(map);

    let downloadButton =
        new ymaps.control.Button({
            data: {
                content: '<b>Скачать</b>',
                title: 'Скачать export.xlsx'
            },
            state: {
                enabled: false
            }
        });
    downloadButton.events.add('press', downloadButtonHandler);
    map.controls.add(downloadButton, {
        float: "left"
    });

    let drawButton =
        new ymaps.control.Button({
            data: {
                content: '<b>Выбрать</b>',
                title: 'Выбрать область'
            }
        });
    drawButton.events.add('select', drawButtonHandler);
    drawButton.events.add('deselect', stopEditingButtonHandler);
    map.controls.add(drawButton, {
        float: "left"
    });

    map.controls.get('fullscreenControl').events.add('fullscreenenter', fullscreenControlEnterHandler);
    map.controls.get('fullscreenControl').events.add('fullscreenexit', fullscreenControlExitHandler);

    function drawButtonHandler() {
        downloadButton.disable();
        drawButton.data.set('content', '<b> Стоп </b>');
        drawButton.data.set('title', 'Отобразить выбранные объекты');

        // Удаляем старый полигон.
        if (polygon) {
            map.geoObjects.remove(polygon);
            polygon = null;
        }


        drawLineOverMap(map)
            .then(function (coordinates) {
                // Переводим координаты из 0..1 в географические.
                var bounds = map.getBounds();
                coordinates = coordinates.map(function (x) {
                    return [
                        // Широта (latitude).
                        // Y переворачивается, т.к. на canvas'е он направлен вниз.
                        bounds[0][0] + (1 - x[1]) * (bounds[1][0] - bounds[0][0]),
                        // Долгота (longitude).
                        bounds[0][1] + x[0] * (bounds[1][1] - bounds[0][1]),
                    ];
                });

                // Тут надо симплифицировать линию.

                coordinates = coordinates.filter(function (_, index) {
                    return index % 3 === 0;
                });

                // Создаем новый полигон
                polygon = new ymaps.Polygon([coordinates], {}, polygonOptions);
                map.geoObjects.add(polygon);
            }).then(function () {
                polygon.editor.startEditing();
        });
    }

    function downloadButtonHandler() {
        const rows = [];

        if (typeof objectsInsidePolygon == "undefined" || objectsInsidePolygon.getLength() == 0) {
            alert('Нечего выгружать');
            return;
        }

        objectsInsidePolygon.each(function (a, b) {
            let prop = a.properties._data;
            rows.push(prop.geoPointId);
        });

        $('#id_list').val(rows.join(','));
        $('#export_geopoints').submit();
    }

    function fullscreenControlEnterHandler(event) {
        isMapFullScreen = true;
        let canvas = $('#draw-canvas').detach();
        $('body').append(canvas);
        canvas.css({"z-index": 11000});
    }

    function fullscreenControlExitHandler(event) {
        isMapFullScreen = false;
        let canvas = $('#draw-canvas').detach();
        $('#container').append(canvas);
        canvas.css({"z-index": "auto"});
    }

    function stopEditingButtonHandler(event) {
        polygon.editor.stopEditing();

        // покажем только те объекты, что в выбранной области
        storage.setOptions('visible', false);
        objectsInsidePolygon = storage.searchInside(polygon);
        objectsInsidePolygon.setOptions('visible', true);

        downloadButton.enable();
        drawButton.data.set('content', '<b>Выбрать</b>');
        drawButton.data.set('title', 'Выбрать область');
    }
}

function drawLineOverMap(map) {
    var canvas = document.querySelector('#draw-canvas');
    var ctx2d = canvas.getContext('2d');
    var drawing = false;
    var coordinates = [];

    // Задаем размеры канвасу как у карты.
    var rect = map.container.getParentElement().getBoundingClientRect();
    if (isMapFullScreen) {
        rect = document.querySelector('body > ymaps').getBoundingClientRect();
    }
    canvas.style.width = rect.width + 'px';
    canvas.style.height = rect.height + 'px';
    canvas.width = rect.width;
    canvas.height = rect.height;

    // Применяем стили.
    ctx2d.strokeStyle = canvasOptions.strokeStyle;
    ctx2d.lineWidth = canvasOptions.lineWidth;
    canvas.style.opacity = canvasOptions.opacity;

    ctx2d.clearRect(0, 0, canvas.width, canvas.height);

    // Показываем канвас. Он будет сверху карты из-за position: absolute.
    canvas.style.display = 'block';

    canvas.onmousedown = function (e) {
        // При нажатии мыши запоминаем, что мы начали рисовать и координаты.
        drawing = true;
        coordinates.push([e.offsetX, e.offsetY]);
    };

    canvas.onmousemove = function (e) {
        // При движении мыши запоминаем координаты и рисуем линию.
        if (drawing) {
            var last = coordinates[coordinates.length - 1];
            ctx2d.beginPath();
            ctx2d.moveTo(last[0], last[1]);
            ctx2d.lineTo(e.offsetX, e.offsetY);
            ctx2d.stroke();

            coordinates.push([e.offsetX, e.offsetY]);
        }
    };

    return new Promise(function (resolve) {
        // При отпускании мыши запоминаем координаты и скрываем канвас.
        canvas.onmouseup = function (e) {
            coordinates.push([e.offsetX, e.offsetY]);
            canvas.style.display = 'none';
            drawing = false;

            coordinates = coordinates.map(function (x) {
                return [x[0] / canvas.width, x[1] / canvas.height];
            });

            resolve(coordinates);
        };
    });
}
