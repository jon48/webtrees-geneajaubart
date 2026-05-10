/**
 * JS Library - MyArtJaub Entry Helper module
 *
 * webtrees-MyArtJaub
 * Copyright (C) 2024-2026 Jonathan Jaubart
 *
 * Based on webtrees: online genealogy
 * Copyright (C) 2009-2026 webtrees development team
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

/* global $, majEntryHelper, MAJ_ENTRYHELPER_TRANSLATIONS */

// Add required icons to Font-Awesome
import { library } from '@fortawesome/fontawesome-svg-core';

import { faPaste } from '@fortawesome/free-regular-svg-icons';

library.add(faPaste);

// MyArtJaub Entry Helper library
(function (majEntryHelper, translations) {
  // Merge provided translations with default ones.
  const I18N = $.extend({
    citationLabel: 'Source citation',
    citationCopy: 'Copy source citation',
    citationPaste: 'Fill source citation from last copy'
  }, translations);

  /** Private functions */

  /**
     * Retrieve the value of the input element identified by the elementId provided.
     *
     * @param {string} elementId
     * @returns {string}
     */
  const getInputValue = elementId => document.getElementById(elementId)?.value || null;

  /**
     * Set the value of the input element identified by the elementId provided.
     *
    * @param {string} elementId
    * @param {string} value
     */
  const setInputValue = (elementId, value) => {
    const inputElement = document.getElementById(elementId);
    if (inputElement !== null) {
      inputElement.value = value;
    }
  };

  /**
     * Retrieve the TomSelect object identified by the elementId provided.
     *
     * @param {string} elementId
     * @returns {Object|null}
     */
  const getTomSelect = elementId => document.getElementById(elementId)?.tomselect;

  /**
     * Retrieve the current value and text of the TomSelect object identified by the elementId provided.
     *
    * @param {string} elementId
    * @returns {{value: string|null, text: string| null}}
     */
  const getTomSelectValues = elementId => {
    const ts = getTomSelect(elementId);
    const tsValue = ts.getValue() || null;
    return { value: tsValue, text: ts.getItem(tsValue)?.textContent || null };
  };

  /**
     * Set the value of the TomSelect object identified by the elementId provided.
     *
    * @param {string} elementId
    * @param {string} tsItemValue
    * @param {string} tsItemText
     */
  const setTomSelectValues = (elementId, tsItemValue, tsItemText) => {
    const ts = getTomSelect(elementId) || null;

    if (ts !== null && tsItemValue !== null && tsItemText !== null) {
      ts.addOption({ value: tsItemValue, text: tsItemText }, true);
      ts.setValue(tsItemValue);
    }
  };

  /**
     * Retrieve the current value of the TypeAhead object identified by the elementId provided.
     *
    * @param {string} elementId
    * @returns {string| null}
     */
  const getTypeAheadValue = elementId => $('#' + elementId).typeahead('val') || null;

  /**
     * Set the value of the TypeAhead object identified by the elementId provided.
     *
    * @param {string} elementId
    * @param {string} value
     */
  const setTypeAheadValue = (elementId, value) => {
    const tAhead = $('#' + elementId);
    tAhead.typeahead('val', value);
    tAhead.trigger('change');
  };
    /**
     * Cumpute the elements ID relevant to the source citation identified by the provided element ID.
     *
    * @param {string} elementId
    * @returns {{citationDetailsId: string| null, citationPageId: string|null, citationDateId: string|null, citationActCityId: string|null, citationActeFileId: string|null}}
     */
  const getSourceCitationsElementIds = elementId => {
    const citationLabel = $('#' + elementId + '-ts-label');
    const citationDetailEl = citationLabel.parent().next('.wt-nested-edit-fields');
    const citationActId = citationDetailEl.find(".certificate-edit-group input[type='hidden']").first().attr('id') || null;

    return {
      citationDetailsId: citationDetailEl.attr('id'),
      citationPageId: citationDetailEl.find("label[for$='SOUR-PAGE']").first().attr('for') || null,
      citationDateId: citationDetailEl.find("label[for$='SOUR-DATA-DATE']").first().attr('for') || null,
      citationActCityId: citationActId === null ? null : citationActId + '-city',
      citationActeFileId: citationActId === null ? null : citationActId + '-file'
    };
  };

  /**
     * Click action callback
     *
     * @callback clickAction
     */

  /**
     * Generate a link element with Font Awesome icon.
     *
     * @param {string[]} faIconList
     * @param {string} title
     * @param {clickAction} clickAction
     * @return {Element}
     */
  const createFontAwesomeIcon = (faIconList, title, clickAction) => {
    const linkEl = document.createElement('a');
    const iconEl = document.createElement('i');
    iconEl.classList.add(...faIconList);
    linkEl.append(iconEl);
    linkEl.classList.add('btn', 'btn-link');
    linkEl.title = title;
    linkEl.onclick = clickAction;
    return linkEl;
  };

  /**
     * Callback to add copy & paste icons next to source citation labels.
     *
     * @param {Element} labelElement
     */
  const addCitationFillIcons = (_, labelElement) => {
    const srcId = labelElement.getAttribute('for').replace('-ts-label', '');

    labelElement.append(createFontAwesomeIcon(['far', 'fa-copy', 'fa-fw'], I18N.citationCopy, () => majEntryHelper.persistSourceCitation(srcId)));
    labelElement.append(createFontAwesomeIcon(['far', 'fa-paste', 'fa-fw'], I18N.citationPaste, () => majEntryHelper.fillSourceCitation(srcId)));
  };

  /** API Functions */

  /**
     * Add icons to Source Citation labels
     */
  majEntryHelper.addAutoFillIcons = () => $("form label:contains('" + I18N.citationLabel + "')").each(addCitationFillIcons);

  /**
     * Store into session the data collected from the source citation identified by the provided element ID.
     *
     * @param {string} elementId
     */
  majEntryHelper.persistSourceCitation = (elementId) => {
    const srcElements = getSourceCitationsElementIds(elementId);
    const factSource = getTomSelectValues(elementId);
    const citationActCity = getTomSelectValues(srcElements.citationActCityId);

    if (factSource.value === null) {
      window.sessionStorage.removeItem('maj-misc-citation-last');
    } else {
      window.sessionStorage.setItem('maj-misc-citation-last', JSON.stringify({
        sourceId: factSource.value,
        sourceName: factSource.text,
        citationPage: getTypeAheadValue(srcElements.citationPageId),
        citationDate: getInputValue(srcElements.citationDateId),
        citationActCityId: citationActCity.value,
        citationActCity: citationActCity.text,
        citationActFile: getTypeAheadValue(srcElements.citationActeFileId)
      }));
    }
  };

  /**
     * Populate the source citation identified by the provided element ID with data stored in session.
     *
     * @param {string} elementId
     */
  majEntryHelper.fillSourceCitation = (elementId) => {
    const srcElements = getSourceCitationsElementIds(elementId);

    const storedCitation = JSON.parse(window.sessionStorage.getItem('maj-misc-citation-last')) || {};

    if (srcElements.citationDetailsId !== null) $('#' + srcElements.citationDetailsId).collapse('show');
    setTomSelectValues(elementId, storedCitation.sourceId || null, storedCitation.sourceName || null);
    setTypeAheadValue(srcElements.citationPageId, storedCitation.citationPage || null);
    setInputValue(srcElements.citationDateId, storedCitation.citationDate || null);
    setTomSelectValues(srcElements.citationActCityId, storedCitation.citationActCityId || null, storedCitation.citationActCity || null);
    setTypeAheadValue(srcElements.citationActeFileId, storedCitation.citationActFile || null);
  };
}(window.majEntryHelper = window.majEntryHelper || {}, MAJ_ENTRYHELPER_TRANSLATIONS || {}));

majEntryHelper.addAutoFillIcons();
