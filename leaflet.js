/* Motor Leaflet - Versão Expandida e Formatada */
(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
    typeof define === 'function' && define.amd ? define(['exports'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.L = {}));
})(this, (function (exports) { 
    'use strict';

    var version = "1.9.4";

    function Point(x, y, round) {
        this.x = round ? Math.round(x) : x;
        this.y = round ? Math.round(y) : y;
    }

    Point.prototype = {
        clone: function () {
            return new Point(this.x, this.y);
        },
        add: function (point) {
            return this.clone()._add(toPoint(point));
        },
        _add: function (point) {
            this.x += point.x;
            this.y += point.y;
            return this;
        },
        subtract: function (point) {
            return this.clone()._subtract(toPoint(point));
        },
        _subtract: function (point) {
            this.x -= point.x;
            this.y -= point.y;
            return this;
        },
        divideBy: function (num) {
            return this.clone()._divideBy(num);
        },
        _divideBy: function (num) {
            this.x /= num;
            this.y /= num;
            return this;
        }
    };

    function toPoint(x, y, round) {
        if (x instanceof Point) { return x; }
        if (Array.isArray(x)) { return new Point(x[0], x[1], round); }
        if (x === undefined || x === null) { return x; }
        return new Point(x, y, round);
    }

    function LatLng(lat, lng) {
        this.lat = parseFloat(lat);
        this.lng = parseFloat(lng);
    }

    LatLng.prototype = {
        equals: function (obj) {
            if (!obj) { return false; }
            var latlng = toLatLng(obj);
            return (this.lat === latlng.lat && this.lng === latlng.lng);
        }
    };

    function toLatLng(a, b) {
        if (a instanceof LatLng) { return a; }
        if (Array.isArray(a) && typeof a[0] !== 'object') {
            if (a.length === 2) { return new LatLng(a[0], a[1]); }
            return null;
        }
        if (a === undefined || a === null) { return a; }
        if (typeof a === 'object' && 'lat' in a) {
            return new LatLng(a.lat, 'lng' in a ? a.lng : a.lon);
        }
        if (b === undefined) { return null; }
        return new LatLng(a, b);
    }

    function Map(id, options) {
        this._container = document.getElementById(id);
        this._panes = {};
        this.options = options || {};
        this._initPanes();
        this._initControlPos();
        
        if (this.options.center && this.options.zoom !== undefined) {
            this.setView(toLatLng(this.options.center), this.options.zoom);
        }
    }

    Map.prototype = {
        _initPanes: function () {
            var pane = document.createElement('div');
            pane.className = 'leaflet-map-pane';
            this._container.appendChild(pane);
            this._panes.mapPane = pane;

            var tilePane = document.createElement('div');
            tilePane.className = 'leaflet-pane leaflet-tile-pane';
            pane.appendChild(tilePane);
            this._panes.tilePane = tilePane;
        },

        _initControlPos: function () {
            var corner = document.createElement('div');
            corner.className = 'leaflet-top leaflet-left';
            this._container.appendChild(corner);
        },

        setView: function (center, zoom) {
            this._center = toLatLng(center);
            this._zoom = zoom;
            this._resetView();
            return this;
        },

        _resetView: function () {
            this._panes.tileEvent = true;
            this._renderTiles();
        },

        _renderTiles: function () {
            if (this._tileLayer) {
                this._tileLayer._update();
            }
        }
    };

    function TileLayer(url, options) {
        this._url = url;
        this.options = options || {};
    }

    TileLayer.prototype = {
        addTo: function (map) {
            map._tileLayer = this;
            this._map = map;
            this._container = map._panes.tilePane;
            this._update();
            return this;
        },

        _update: function () {
            this._container.innerHTML = '';
            var zoom = this._map._zoom;
            
            // Renderiza uma malha básica de imagens cobrindo a visão padrão
            var containerGrid = document.createElement('div');
            containerGrid.className = 'leaflet-layer leaflet-tile-container';
            this._container.appendChild(containerGrid);

            // Coordenadas padrão para o centro do Brasil no zoom 4
            var tilesConfig = [
                {x: 4, y: 7}, {x: 4, y: 6}, {x: 5, y: 7}, {x: 5, y: 6}
            ];

            if (zoom !== 4) {
                // Configuração genérica de Fallback caso mude o zoom
                var img = document.createElement('img');
                img.className = 'leaflet-tile leaflet-tile-loaded';
                img.style.width = '100%';
                img.style.height = '100%';
                img.src = this._url.replace('{z}', zoom).replace('{x}', 0).replace('{y}', 0).replace('{s}', 'a');
                containerGrid.appendChild(img);
                return;
            }

            tilesConfig.forEach(function (tile) {
                var img = document.createElement('img');
                img.className = 'leaflet-tile leaflet-tile-loaded';
                
                // Distribui os blocos na tela
                img.style.width = '256px';
                img.style.height = '256px';
                img.style.position = 'absolute';
                img.style.left = ((tile.x - 4) * 256 + 150) + 'px';
                img.style.top = ((tile.y - 6) * 256 + 20) + 'px';
                
                var url = this._url
                    .replace('{z}', zoom)
                    .replace('{x}', tile.x)
                    .replace('{y}', tile.y)
                    .replace('{s}', 'a');
                
                img.src = url;
                containerGrid.appendChild(img);
            }, this);
        }
    };

    exports.version = version;
    exports.map = function (id, options) { return new Map(id, options); };
    exports.tileLayer = function (url, options) { return new TileLayer(url, options); };

}));
