/**
 * Styles - MyArtJaub GeoDispersion module
 *
 * webtrees-MyArtJaub
 * Copyright (C) 2021-2023 Jonathan Jaubart
 *
 * Based on webtrees: online genealogy
 * Copyright (C) 2010-2023 webtrees development team
 *
 * This file is part of webtrees-MyArtJaub
 *
 * webtrees-MyArtJaub is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * webtrees-MyArtJaub is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with webtrees-MyArtJaub. If not, see <http://www.gnu.org/licenses/>.
 */

/* global L */

// Add required icons to Font-Awesome
import { library } from '@fortawesome/fontawesome-svg-core';

import {
  faMapMarkedAlt, faTable
} from '@fortawesome/free-solid-svg-icons';

library.add(faMapMarkedAlt, faTable);

// MyArtJaub GeoDispersion library
(function (majGeodispersion) {
  /** API object */

  /**
   * Feature Style object
   *
   * @param {{default: Object, stroke: Object, lowColor: Object, maxColor: Object, hover: Object}} colorConfig
   */
  majGeodispersion.MapFeaturesStyle = function (colorConfig) {
    /**
     * Returns features default style
     *
     * @returns {Object}
     */
    function defaultStyle () {
      return {
        fillColor: colorString(colorConfig.defaultColor),
        weight: 1,
        opacity: 1,
        color: colorString(colorConfig.strokeColor),
        fillOpacity: 1
      };
    }

    /**
     * Returns features hover style
     *
     * @returns {Object}
     */
    function hoverStyle () {
      return { ...defaultStyle(), fillColor: colorString(colorConfig.hoverColor) };
    }

    /**
     * Returns style for a feature based on its data
     *
     * @returns {Object}
     */
    function featureStyle (feature) {
      return feature.properties.color_ratio !== undefined
        ? {
            ...defaultStyle(),
            fillColor: colorString(
              intermediateColor(
                feature.properties.color_ratio,
                colorConfig.lowColor,
                colorConfig.highColor
              )
            )
          }
        : defaultStyle();
    }

    return {
      colorConfig,
      defaultStyle,
      hoverStyle,
      featureStyle
    };
  };

  /** API Functions */

  /**
   * Draws a Leaflet map at a specified element
   *
   * @param {string} elementId
   * @param {{i18n: Object, icons: Object, mapProviders: Object}} config
   * @returns {MapObject}
   */
  majGeodispersion.drawMap = function (elementId, config) {
    const FullscreenControl = L.Control.extend({
      options: {
        position: 'topleft'
      },
      onAdd: (map) => {
        const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
        const anchor = L.DomUtil.create('a', 'leaflet-control-fullscreen', container);

        anchor.setAttribute('role', 'button');
        anchor.dataset.wtFullscreen = '.maj-geodisp-fullscreen-container';
        anchor.innerHTML = config.icons.fullScreen;

        return container;
      }
    });

    const dataLayer = new L.FeatureGroup();

    let defaultLayer = null;

    for (const [, provider] of Object.entries(config.mapProviders)) {
      for (const [, child] of Object.entries(provider.children)) {
        if ('bingMapsKey' in child) {
          child.layer = L.tileLayer.bing(child);
        } else {
          child.layer = L.tileLayer(child.url, child);
        }
        if (provider.default && child.default) {
          defaultLayer = child.layer;
        }
      }
    }

    if (defaultLayer === null) {
      console.log('No default map layer defined - using the first one.');
      defaultLayer = config.mapProviders[0].children[0].layer;
    }

    const map = L.map(elementId, {
      center: [0, 0],
      zoomSnap: 0,
      zoomControl: false, // disable zoom
      scrollWheelZoom: false // disable zoom
    })
      .addLayer(defaultLayer)
      .addLayer(dataLayer)
      .addControl(new FullscreenControl())
      .addControl(L.control.layers.tree(config.mapProviders, null, {
        closedSymbol: config.icons.expand,
        openedSymbol: config.icons.collapse
      }));

    return new MapObject(map, dataLayer);
  };

  /**
   * Adds a GeoJson data layer to the map
   *
   * @param {MapObject} map
   * @param {Object} data
   * @param {MapFeaturesStyle} style
   */
  majGeodispersion.addGeoJsonDataToMap = function (map, data, style) {
    if (map.dataLayer === undefined || map.leafletMap === undefined) return;

    const geoJsonLayer = L.geoJson(data, {
      onEachFeature: function (feature, layer) {
        if (feature.properties.tooltip !== undefined) {
          layer.bindTooltip(feature.properties.tooltip);

          layer.on({
            mouseover: () => layer.setStyle(style.hoverStyle()),
            mouseout: () => geoJsonLayer.resetStyle(layer)
          });
        }
      },
      style: style.featureStyle
    });

    if (data.features.length > 0) {
      map.dataLayer.addLayer(geoJsonLayer);
    }
    map.leafletMap.fitBounds(map.dataLayer.getBounds(), { padding: [50, 50] });
  };

  /** Private Objects */

  /**
   * @private
   * @param {Map} map
   * @param {FeatureGroup} dataLayer
   * @returns {{leafletMap: Map, dataLayer: FeatureGroup}}
   */
  function MapObject (map, dataLayer) {
    this.leafletMap = map;
    this.dataLayer = dataLayer;
  }

  /** Private Functions */
  /**
   * @private
   * @param {{r: number, g: number, b; number}} color
   * @returns {string}
   */
  function colorString (color) {
    return 'rgb(' + [color.r, color.g, color.b].join(',') + ')';
  }

  /**
   * @private
   * @param {number} pct
   * @param {{r: number, g: number, b; number}} lowColor
   * @param {{r: number, g: number, b; number}} highColor
   * @returns {{r: number, g: number, b; number}}
   */
  function intermediateColor (pct, lowColor, highColor) {
    const pctLower = 1 - pct;
    const pctUpper = pct;
    return {
      r: Math.floor(lowColor.r * pctLower + highColor.r * pctUpper),
      g: Math.floor(lowColor.g * pctLower + highColor.g * pctUpper),
      b: Math.floor(lowColor.b * pctLower + highColor.b * pctUpper)
    };
  }
}(window.majGeodispersion = window.majGeodispersion || {}));
