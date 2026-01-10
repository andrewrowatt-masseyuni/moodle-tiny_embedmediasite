// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * TODO describe module ui
 *
 * @module     tiny_embedmediasite/ui
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import EmbedMediasiteModal from './modal';
import Templates from 'core/templates';
import {exception as displayException} from 'core/notification';
import {getMyMediasitePresentations} from './repository';
// import ModalEvents from 'core/modal_events';
// import {get_strings as getStrings} from 'core/str';

/**
 * Handle action
 *
 * @param {TinyMCE} editor
 */
export const handleAction = async(editor) => {
    displayDialogue(editor);
};

/**
 * Display modal
 *
 * @param  {TinyMCE} editor
 */
const displayDialogue = async(editor) => {
    const data = Object.assign({}, {});

    // Show modal with buttons.
    const modal = await EmbedMediasiteModal.create({
        templateContext: await getTemplateContext(editor, data),
        large: true,
        removeOnClose: true,
    });

    await modal.show();

    // const contentContainer = document.getElementById('tiny_embedmediasite_content-container');
    const loadingIndicator = document.getElementById('tiny_embedmediasite_loading');
    let page = 1; // Track which "page" of data to load

    /**
     * Load the second and subsequent pages of content.
     *
     * @param {*} pageNumber
     */
    function loadMoreContent(pageNumber) {
        const presentations = getMyMediasitePresentations(pageNumber);
        presentations.then(newPresentations => {
            // eslint-disable-next-line promise/no-nesting
            Templates.renderForPromise('tiny_embedmediasite/_presentations',
                {presentations: newPresentations}).then(({html, js}) => {
                    Templates.appendNodeContents('#tiny_embedmediasite_content-container', html, js);
                return newPresentations;
            })
            // Deal with this exception (Using core/notify exception function is recommended).
            .catch((error) => displayException(error));

            return newPresentations;
        }).catch(error => {
            window.console.error('Error loading more content:', error);
        });
    }

    // Set up the Intersection Observer
    const observer = new IntersectionObserver(entries => {
        window.console.log('IntersectionObserver', entries[0]);
        // Check if the loading indicator is visible
        if (entries[0].isIntersecting) {
            // Stop observing temporarily to prevent multiple calls while loading
            observer.unobserve(loadingIndicator);

            page++;
            loadMoreContent(page);

            // Re-observe the indicator after a short delay (or after fetch completes)
            setTimeout(() => {
                observer.observe(loadingIndicator);
            }, 500);

            // Optional: In a real app, if no more data is available,
            // you would stop observing the target permanently and hide the indicator.
        }
    }, {
        root: null, // Observe the viewport
        threshold: 1.0, // Trigger when 100% of the indicator is visible
        rootMargin: '0px'
    });

    // Start observing the loading indicator element
    observer.observe(loadingIndicator);
};

/**
 * Get the template context for the dialogue.
 *
 * @param {Editor} editor
 * @param {object} data
 * @returns {object} data
 */
const getTemplateContext = async(editor, data) => {
    const page = 1;
    return Object.assign({}, {
        presentations: await getMyMediasitePresentations(page),
    }, data);
};
