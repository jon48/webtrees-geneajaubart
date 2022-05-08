/**
 * Styles - MyArtJaub Sosa module
 *
 * webtrees-MyArtJaub
 * Copyright (C) 2009-2021 Jonathan Jaubart
 *
 * Based on webtrees: online genealogy
 * Copyright (C) 2020 webtrees development team
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

import { library } from '@fortawesome/fontawesome-svg-core';

import {
  faCircle, faSpinner
} from '@fortawesome/free-solid-svg-icons';

library.add(faCircle, faSpinner);

// MyArtJaub Sosa library
(function (majSosa) {
  /** majSosa.colors namespace */
  (function (colors) {
    /**
     * Convert colors in hexadecimal format to rgb format
     * @param {string} color
     * @returns {number[]}
     */
    function hexToRgb (color) {
      color = color.match(/^#?(?<color>[0-9a-f]{6}|[0-9a-f]{3})$/i)?.groups.color ?? '';
      if (color.length === 3) {
        return color.split('').map(c => parseInt(c + c, 16));
      }
      if (color.length === 6) {
        return color.match(/[0-9a-f]{2}/gi).map(c => parseInt(c, 16));
      }
      throw new Error('Hexadecimal color is not in the correct format');
    }

    /**
     * Convert colors in rgb format to hexadecimal format
     * @param {number} r
     * @param {number} g
     * @param {number} b
     * @returns {string}
     */
    function rgbToHex (r, g, b) {
      if ([r, g, b].every(c => Number.isInteger(c) && c >= 0 && c <= 255)) {
        return '#' + [r, g, b].map(c => c.toString(16).padStart(2, '0')).join('');
      }
      throw new Error('Invalid color values');
    }

    /**
     * Interpolate intermediary colors between 2 colors
     * @param {string} startColor
     * @param {string} endColor
     * @param {number} steps
     * @returns {string[]}
     */
    colors.interpolateRgb = function (startColor, endColor, steps) {
      const s = hexToRgb(startColor);
      const e = hexToRgb(endColor);
      const factorR = (e[0] - s[0]) / steps;
      const factorG = (e[1] - s[1]) / steps;
      const factorB = (e[2] - s[2]) / steps;

      const colors = [];
      for (let x = 1; x < steps; x++) {
        colors.push(rgbToHex(
          Math.round(s[0] + factorR * x),
          Math.round(s[1] + factorG * x),
          Math.round(s[2] + factorB * x)
        ));
      }
      colors.push(rgbToHex(e[0], e[1], e[2]));

      return colors;
    };

    /**
     * Evaluate colors based on CSS variables, and fallback to default colors.
     * @param {object} colors
     * @returns {string[]}
     */
    colors.fromCss = function (colors) {
      for (let i = 0; i < colors.length; i++) {
        if (Array.isArray(colors[i])) {
          if (colors[i].length === 2) {
            const color = window.getComputedStyle(document.documentElement).getPropertyValue(colors[i][0]).trim();
            colors[i] = color.length > 0 ? color : colors[i][1];
          } else {
            colors[i] = '#ffffff';
          }
        }
      }
      return colors;
    };
  })(majSosa.colors = majSosa.colors || {});
}(window.majSosa = window.majSosa || {}));
