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

ymaps.ready(init);

function init() {
    var map = new ymaps.Map('map', {
        center: [55.0378599938, 73.4201805827],
        zoom: 15
    });
    var polygon = null;

    var drawButton = document.querySelector('#draw');
    var downloadButton = document.querySelector('#download');

    drawButton.onclick = function () {
        drawButton.disabled = true;

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


                drawButton.disabled = false;
            }).then(function () {

            // покажем только те объекты, что в выбранной области
            storage.setOptions('visible', false);
            window.objectsInsidePolygon = storage.searchInside(polygon);
            objectsInsidePolygon.setOptions('visible', true);
        });
    };

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
                    }
                }
            )
        );
    });

    var storage = ymaps.geoQuery(allObjects).setOptions('visible', false).addToMap(map);

    downloadButton.onclick = makeCsvAndDownload;
}

function makeCsvAndDownload() {
    const rows = [];

    if (typeof objectsInsidePolygon == "undefined" || objectsInsidePolygon.getLength() == 0) {
        alert('Нечего выгружать');
        return;
    }

    objectsInsidePolygon.each(function (a, b) {
        let prop = a.properties._data;
        let row = [
            b + 1,
            prop.hintContent,
            prop.baloonContent
        ];
        rows.push(row);
    });

    exportToCsv('export.csv', rows);
}

function exportToCsv(filename, rows) {
    var processRow = function (row) {
        var finalVal = '';
        for (var j = 0; j < row.length; j++) {
            var innerValue = row[j] === null ? '' : row[j].toString();
            if (row[j] instanceof Date) {
                innerValue = row[j].toLocaleString();
            }
            var result = innerValue.replace(/"/g, '""');
            if (result.search(/("|,|\n)/g) >= 0)
                result = '"' + result + '"';
            if (j > 0)
                finalVal += ';';
            finalVal += result;
        }
        return finalVal + '\n';
    };

    var universalBOM = "\uFEFF";

    var csvFile = universalBOM;
    for (var i = 0; i < rows.length; i++) {
        csvFile += processRow(rows[i]);
    }

    var blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
    if (navigator.msSaveBlob) { // IE 10+
        navigator.msSaveBlob(blob, filename);
    } else {
        var link = document.createElement("a");
        if (link.download !== undefined) { // feature detection
            // Browsers that support HTML5 download attribute
            var url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
}

function drawLineOverMap(map) {
    var canvas = document.querySelector('#draw-canvas');
    var ctx2d = canvas.getContext('2d');
    var drawing = false;
    var coordinates = [];

    // Задаем размеры канвасу как у карты.
    var rect = map.container.getParentElement().getBoundingClientRect();
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
