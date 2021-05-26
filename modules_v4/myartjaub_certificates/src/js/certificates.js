/**
 * Styles - MyArtJaub Certificates module
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

'use strict';

/* global $, Bloodhound, majCertificates */

// Add required icons to Font-Awesome
import { library } from '@fortawesome/fontawesome-svg-core';

import {
  faScroll
} from '@fortawesome/free-solid-svg-icons';

library.add(faScroll);

// MyArtJaub Certificates library
(function (majCertificates) {
  /**
   * Initialize autocomplete elements.
   * @param {string} selector
   */
  majCertificates.autocomplete = function (selector) {
    // Use typeahead/bloodhound for autocomplete
    $(selector).each(function () {
      const that = this;
      $(this).typeahead(null, {
        display: 'value',
        limit: 10,
        minLength: 2,
        source: new Bloodhound({
          datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
          queryTokenizer: Bloodhound.tokenizers.whitespace,
          remote: {
            url: this.dataset.autocompleteCertifUrl,
            replace: function (url, uriEncodedQuery) {
              const element = that.closest('.form-group').querySelector('select');
              const extra = element.options[element.selectedIndex].value;
              const symbol = (url.indexOf('?') > 0) ? '&' : '?';
              return url.replace(/(%7B|{)query(%7D|})/, uriEncodedQuery) + symbol + 'cityobf=' + encodeURIComponent(extra);
            },
            wildcard: '{query}'
          }
        })
      });
    });
  };
}(window.majCertificates = window.majCertificates || {}));

// Autocomplete
majCertificates.autocomplete('input[data-autocomplete-certif-url]');
